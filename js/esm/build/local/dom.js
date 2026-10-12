/**
 * Typed access to named controls on native Moodle forms.
 *
 * @module local_filebank/local/dom
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const o=(n,t)=>{const e=n.elements.namedItem(t);if(!(e instanceof HTMLInputElement||e instanceof HTMLSelectElement||e instanceof HTMLTextAreaElement))throw new TypeError("Missing native form control: "+t);return e};export{o as formControl};
