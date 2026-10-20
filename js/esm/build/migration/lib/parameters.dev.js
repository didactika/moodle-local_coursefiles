var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The parameters of a migration: where they start from and when they are complete.
 *
 * @module     local_filebank/migration/lib/parameters
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const MAX_CORRESPONDENCES = 20;
const MAX_SECTION_PAIRS = 200;
const sameNameCorrespondence = /* @__PURE__ */ __name(() => ({
  sourcetype: "folder",
  sourcefield: "name",
  sourcemethod: "pattern",
  sourcevalue: "~^(?<name>.+)$~",
  targetfield: "name",
  targetmethod: "template",
  targetvalue: "${name}"
}), "sameNameCorrespondence");
const profileRules = /* @__PURE__ */ __name((option) => JSON.parse(option.rules), "profileRules");
const parametersFor = /* @__PURE__ */ __name((option) => ({
  profileid: option.id,
  source: {},
  target: {},
  rules: profileRules(option)
}), "parametersFor");
const parametersOf = /* @__PURE__ */ __name((migration) => {
  const values = /* @__PURE__ */ __name((list) => {
    const map = {};
    list.forEach((filter) => {
      if (filter.value !== "") {
        map[filter.key] = filter.value;
      }
    });
    return map;
  }, "values");
  return {
    profileid: migration.profileid,
    source: values(migration.filters.source),
    target: values(migration.filters.target),
    rules: JSON.parse(migration.rules)
  };
}, "parametersOf");
const complete = /* @__PURE__ */ __name((row) => row.sourcevalue.trim() !== "" && row.targetvalue.trim() !== "", "complete");
const problemsOf = /* @__PURE__ */ __name((parameters, dimensions) => {
  const problems = [];
  if (parameters.profileid <= 0) {
    problems.push("profile");
  }
  if (dimensions > 0 && Object.keys(parameters.source).length + Object.keys(parameters.target).length === 0) {
    problems.push("courses");
  }
  if (parameters.rules.correspondences.length === 0 || !parameters.rules.correspondences.every(complete)) {
    problems.push("correspondence");
  }
  if (parameters.rules.sectionstrategy === "mapping") {
    const pairs = parameters.rules.sectionmap;
    const valid = pairs.length > 0 && pairs.every((pair) => /^[1-9][0-9]*$/.test(pair.source) && /^[1-9][0-9]*$/.test(pair.target));
    if (!valid) {
      problems.push("sections");
    }
  }
  return problems;
}, "problemsOf");
const keyOf = /* @__PURE__ */ __name((parameters) => JSON.stringify(parameters), "keyOf");
export {
  MAX_CORRESPONDENCES,
  MAX_SECTION_PAIRS,
  keyOf,
  parametersFor,
  parametersOf,
  problemsOf,
  profileRules,
  sameNameCorrespondence
};
//# sourceMappingURL=parameters.dev.js.map
