import{loadLegacy as t}from"./amd";/**
 * Typed adapter for Moodle APIs without an equivalent native ESM module.
 *
 * @module local_coursefiles/compat/editor
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const e=await t("editor_tiny/editor"),o=e.getInstanceForElementId;export{o as getInstanceForElementId};
