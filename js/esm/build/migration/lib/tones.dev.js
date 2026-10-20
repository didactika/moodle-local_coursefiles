var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The class that gives a colour to a badge or an alert. An alert is the one of Bootstrap, so the theme styles it as it styles the rest
 * of the site; a badge is tinted by the plugin from the colours of the theme, and its names are the ones of Bootstrap 5 only because
 * Moodle 5 flags the ones of Bootstrap 4 as deprecated (the rules of the plugin give them the same look on Bootstrap 4).
 *
 * @module     local_filebank/migration/lib/tones
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const BADGES = {
  primary: "tone-primary",
  success: "tone-success",
  warning: "tone-warning",
  danger: "tone-danger",
  info: "tone-info",
  neutral: "tone-neutral"
};
const ALERTS = {
  primary: "alert-primary",
  success: "alert-success",
  warning: "alert-warning",
  danger: "alert-danger",
  info: "alert-info",
  neutral: "alert-secondary"
};
const badgeClasses = /* @__PURE__ */ __name((tone) => `badge rounded-pill ${BADGES[tone]}`, "badgeClasses");
const alertClasses = /* @__PURE__ */ __name((tone) => `alert ${ALERTS[tone]}`, "alertClasses");
export {
  alertClasses,
  badgeClasses
};
//# sourceMappingURL=tones.dev.js.map
