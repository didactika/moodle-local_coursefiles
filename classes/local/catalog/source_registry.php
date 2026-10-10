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
 * MultiPicker native material import integration.
 *
 * @package local_multipicker
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_multipicker\local\catalog;

/**
 * Request-local registry of declared public activity providers.
 */
final class source_registry {
    /** @var array|null Registered module providers. */
    private static ?array $providers = null;

    /**
     * Return providers, including integrations declared through Moodle's plugin callbacks.
     *
     * @return array Providers keyed by installed module name.
     */
    private static function providers(): array {
        if (self::$providers !== null) {
            return self::$providers;
        }
        $providers = [];
        $content = ['folder' => ['content'], 'resource' => ['content'], 'page' => ['content'],
            'book' => ['chapter'], 'assign' => ['introattachment', 'activityattachment']];
        foreach (\core_component::get_plugin_list('mod') as $module => $directory) {
            $areas = $content[$module] ?? [];
            if (plugin_supports('mod', $module, FEATURE_MOD_INTRO, false)) {
                $areas[] = 'intro';
            }
            if ($areas) {
                $providers[$module] = new native_source_provider($module, $areas);
            }
        }
        foreach (get_plugins_with_function('multipicker_source_providers', 'lib.php') as $plugins) {
            foreach ($plugins as $callback) {
                foreach ($callback() as $module => $provider) {
                    if (!\core_component::get_plugin_directory('mod', $module) || !$provider instanceof source_provider) {
                        throw new \coding_exception('MultiPicker source callbacks must return installed module providers.');
                    }
                    $providers[$module] = $provider;
                }
            }
        }
        return self::$providers = $providers;
    }

    /**
     * Resolve the provider owning a concrete installed activity.
     *
     * @param string $module Activity name.
     * @return source_provider|null Declared provider.
     */
    public static function provider(string $module): ?source_provider {
        return self::providers()[$module] ?? null;
    }

    /**
     * Declare configurable source keys, with a portable wildcard for public introduction areas.
     *
     * @return array Supported component/filearea keys.
     */
    public static function keys(): array {
        $keys = ['mod_*/intro'];
        foreach (self::providers() as $module => $provider) {
            foreach ($provider->areas() as $area) {
                $keys[] = 'mod_' . $module . '/' . $area;
            }
        }
        return array_unique($keys);
    }

    /**
     * Match enabled profile areas without treating private areas as configurable public content.
     *
     * @param array $allowed Profile source keys.
     * @param \stored_file $file Actual source file.
     * @return bool Declared and enabled public area.
     */
    public static function enabled(array $allowed, \stored_file $file): bool {
        return in_array($file->get_component() . '/' . $file->get_filearea(), $allowed, true)
            || ($file->get_filearea() === 'intro' && in_array('mod_*/intro', $allowed, true));
    }

    /**
     * Return translated, context-filtered activity names for chooser filters.
     *
     * @return array Labels keyed by module name.
     */
    public static function module_names(): array {
        $names = [];
        foreach (array_keys(self::providers()) as $module) {
            $names[$module] = get_string('modulename', $module);
        }
        asort($names, SORT_NATURAL | SORT_FLAG_CASE);
        return $names;
    }
}
