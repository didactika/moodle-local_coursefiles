// This file is part of Moodle - https://moodle.org/
// Moodle is free software under the GNU General Public License, version 3 or later.

/**
 * Compile nested component styles with the supported Moodle checkout's Sass dependency.
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {createRequire} from 'node:module';
import {readFileSync, writeFileSync} from 'node:fs';
import {dirname, resolve} from 'node:path';
import {fileURLToPath} from 'node:url';

const args = process.argv.slice(2);
const core = args.find(arg => arg !== '--check') || process.env.MOODLE_ROOT;
if (!core) {
    throw new Error('Usage: node tools/build-styles.mjs <Moodle checkout> [--check]');
}
const require = createRequire(resolve(core, 'package.json'));
const sass = require('sass');
const plugin = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const compiled = sass.compile(resolve(plugin, 'styles.scss'), {style: 'expanded', charset: false});
const output = '/* Generated from styles.scss. Edit the SCSS component sources and rebuild. */\n' + compiled.css + '\n';
const target = resolve(plugin, 'styles.css');
if (args.includes('--check')) {
    if (readFileSync(target, 'utf8') !== output) {
        throw new Error('styles.css is out of date. Run tools/build-styles.mjs without --check.');
    }
} else {
    writeFileSync(target, output);
}
