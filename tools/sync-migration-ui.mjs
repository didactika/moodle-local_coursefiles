// This file is part of Moodle - https://moodle.org/
// Moodle is free software under the GNU General Public License, version 3 or later.

/**
 * Copy the TypeScript of the migration screens from the Moodle 5.3 line, where it is the source, into this one.
 *
 * Usage: node tools/sync-migration-ui.mjs <worktree of main>
 * Then run: node tools/build-migration.mjs
 *
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {cpSync, mkdirSync, rmSync} from 'node:fs';
import {dirname, resolve} from 'node:path';
import {fileURLToPath} from 'node:url';

const main = process.argv[2];
if (!main) {
    throw new Error('Usage: node tools/sync-migration-ui.mjs <worktree of main>');
}
const plugin = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const target = resolve(plugin, 'js/src');
rmSync(resolve(target, 'migration'), {recursive: true, force: true});
mkdirSync(target, {recursive: true});
cpSync(resolve(main, 'js/esm/src/migration'), resolve(target, 'migration'), {recursive: true});
cpSync(resolve(main, 'js/esm/src/Migration.tsx'), resolve(target, 'Migration.tsx'));
console.log('copied the migration screens from ' + main);
