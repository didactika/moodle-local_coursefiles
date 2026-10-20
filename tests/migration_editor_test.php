<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * What does not match never blocks a migration, and what is decided stays in the migration.
 *
 * @package local_filebank
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filebank;

use local_filebank\local\migration\migration_executor;
use local_filebank\local\migration\migration_planner;
use local_filebank\local\migration\plan_editor;
use local_filebank\local\migration\plan_history;
use local_filebank\local\migration\plan_selection;

/**
 * Tests the planning of what does not match, the settlement of conflicts and the changes of parameters.
 *
 * @covers \local_filebank\local\migration\plan_editor
 * @covers \local_filebank\local\migration\migration_planner
 * @covers \local_filebank\local\migration\migration_summary
 * @covers \local_filebank\local\migration\planning_store
 */
final class migration_editor_test extends \advanced_testcase {
    /**
     * The options of a migration from the published materials of 2025 into the target materials of 2026.
     *
     * @param string $source The name of the source folder.
     * @return array The options of the planner.
     */
    private function options(string $source = 'Published materials'): array {
        return ['migration' => ['section_match' => ['strategy' => 'ordinal'], 'missing_target' => 'skip',
            'same_path_different_content' => 'block', 'correspondences' => [[
                'source_module' => ['type' => 'folder', 'field' => 'name', 'equals' => $source],
                'target_module' => ['type' => 'folder', 'field' => 'name', 'equals' => 'Target materials'],
            ]]], 'filters' => ['source' => ['edition' => '2025'], 'target' => ['edition' => '2026']]];
    }

    /**
     * Create the fixture with twelve files in conflict.
     *
     * @return array The fixture.
     */
    private function conflicts(): array {
        $this->resetAfterTest();
        $this->setAdminUser();
        return $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_conflict_fixture(['name' => 'Conflicts']);
    }

    /**
     * How many operations of a migration match some conditions.
     *
     * @param int $planid The migration.
     * @param array $conditions The columns and their values.
     * @return int How many.
     */
    private function operations(int $planid, array $conditions): int {
        global $DB;
        return $DB->count_records('local_filebank_operation', ['planid' => $planid] + $conditions);
    }

    /**
     * The content that a file of the destination folder has.
     *
     * @param array $fixture The fixture.
     * @param string $name The name of the file.
     * @return string Its content.
     */
    private function destination(array $fixture, string $name): string {
        $context = \context_module::instance($fixture['targetcmid']);
        return get_file_storage()->get_file($context->id, 'mod_folder', 'content', 0, '/', $name)->get_content();
    }

    /**
     * Run a migration until it has nothing left to do.
     *
     * @param \stdClass $plan The migration.
     * @return string The state of the run.
     */
    private function run_migration(\stdClass $plan): string {
        global $DB, $CFG;
        $CFG->maintenance_enabled = false;
        $plan = $DB->get_record('local_filebank_plan', ['id' => $plan->id]);
        $runid = migration_executor::queue($plan->id, $plan->digest);
        while (migration_executor::execute($runid)) {
            continue;
        }
        return $DB->get_field('local_filebank_run', 'state', ['id' => $runid]);
    }

    /**
     * Files in conflict stay out of the migration, so they block nothing, and one choice settles all of them in the same migration.
     */
    public function test_conflicts_are_left_as_they_are_together_and_never_block(): void {
        global $DB;
        $fixture = $this->conflicts();
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', '', $this->options());
        $this->assertSame('reviewable', $plan->state);
        $this->assertSame(12, $this->operations($plan->id, ['decision' => 'blocked_content']));
        $this->assertSame(0, $this->operations($plan->id, ['decision' => 'blocked_content', 'included' => 1]));
        $this->assertTrue(plan_history::has_work($plan->id), 'The files that match are not held back by the ones in conflict');
        $plans = $DB->count_records('local_filebank_plan');

        $changed = plan_editor::resolve($plan->id, $plan->digest, 'file', [], 'skip', 'blocked_content');

        $this->assertSame(12, $changed);
        $this->assertSame($plans, $DB->count_records('local_filebank_plan'), 'Settling creates no other migration');
        $this->assertSame(12, $this->operations($plan->id, ['decision' => 'skip_conflict']));
        $this->assertSame(0, $this->operations($plan->id, ['decision' => 'skip_conflict', 'included' => 1]));
        $this->assertSame('completed', $this->run_migration($plan));
        $this->assertSame('Destination version 3', $this->destination($fixture, 'clash-3.txt'));
    }

    /**
     * Files in conflict are overwritten all at once when that is what is chosen, and the exclusions that were made are kept.
     */
    public function test_conflicts_are_overwritten_together_and_exclusions_survive(): void {
        global $DB;
        $fixture = $this->conflicts();
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', '', $this->options());
        $copy = $DB->get_record('local_filebank_operation', ['planid' => $plan->id, 'decision' => 'copy'], '*', IGNORE_MULTIPLE);
        plan_selection::save($plan->id, $plan->digest, [$copy->id], [], 'page');
        $plan = $DB->get_record('local_filebank_plan', ['id' => $plan->id]);

        plan_editor::resolve($plan->id, $plan->digest, 'file', [], 'overwrite', 'blocked_content');

        $this->assertSame(12, $this->operations($plan->id, ['decision' => 'overwrite', 'included' => 1]));
        $this->assertSame(0, $this->operations($plan->id, ['sourcehash' => $copy->sourcehash, 'included' => 1]));
        $this->assertSame('completed', $this->run_migration($plan));
        $this->assertSame('Source version 3', $this->destination($fixture, 'clash-3.txt'));
    }

    /**
     * A migration that was changed elsewhere cannot be settled from what a person was looking at.
     */
    public function test_a_stale_digest_is_refused(): void {
        $fixture = $this->conflicts();
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', '', $this->options());
        $this->expectException(\moodle_exception::class);
        plan_editor::resolve($plan->id, str_repeat('a', 64), 'file', [], 'skip', 'blocked_content');
    }

    /**
     * Counting what a migration would do stores nothing, and changing its parameters keeps the migration it was in.
     */
    public function test_check_stores_nothing_and_reconfigure_keeps_the_migration(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_migration_fixture(['name' => 'Parameters']);
        $nofolder = $this->options('A folder that is not there');

        $summary = migration_planner::check($fixture['profileid'], '2025', '2026', '', $this->options());
        $this->assertTrue($summary['haswork']);
        $this->assertSame(0, $DB->count_records('local_filebank_plan'));
        $nothing = migration_planner::check($fixture['profileid'], '2025', '2026', '', $nofolder);
        $this->assertFalse($nothing['haswork']);
        $this->assertSame(0, $nothing['files']);

        $plan = migration_planner::preview($fixture['profileid'], '2025', '2026', '', $nofolder);
        $this->assertSame('reviewable', $plan->state, 'A migration with nothing to write is not cancelled, it can be changed');
        $this->assertFalse(plan_history::has_work($plan->id));
        $this->assertSame(0, migration_executor::queue($plan->id, $plan->digest));
        $this->assertSame('reviewable', $DB->get_field('local_filebank_plan', 'state', ['id' => $plan->id]));

        plan_editor::reconfigure($plan->id, $plan->digest, '2025', '2026', '', $this->options());

        $this->assertSame(1, $DB->count_records('local_filebank_plan'));
        $this->assertTrue(plan_history::has_work($plan->id));
        $this->assertSame(2, $this->operations($plan->id, ['decision' => 'copy']));
    }

    /**
     * What the source does not have and a destination without courses are counted as information, never as an error.
     */
    public function test_what_does_not_match_is_counted_and_is_not_an_error(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->getDataGenerator()->get_plugin_generator('local_filebank')
            ->create_migration_fixture(['name' => 'Notes']);

        $options = $this->options();
        $options['filters']['target'] = ['edition' => '2099'];
        $plan = migration_planner::preview($fixture['profileid'], '2025', '2099', '', $options);
        $document = json_decode($plan->configjson, true);
        $this->assertSame([], $document['issues']);
        $this->assertSame(1, $document['notes']['nodestinationcourse']);
        $this->assertSame(0, $this->operations($plan->id, []));

        $nofolder = migration_planner::preview($fixture['profileid'], '2025', '2026', '', $this->options('Nothing here'));
        $document = json_decode($nofolder->configjson, true);
        $this->assertSame([], $document['issues']);
        $this->assertSame(1, $document['notes']['nosourcefolder']);
    }
}
