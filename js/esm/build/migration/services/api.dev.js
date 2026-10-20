var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Every web service call of the migration screens, in one place.
 *
 * @module     local_filebank/migration/services/api
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { fetchOne } from "@moodle/lms/core/ajax";
const call = /* @__PURE__ */ __name((name, args) => fetchOne({
  methodname: "local_filebank_migration_" + name,
  args
}), "call");
const filters = /* @__PURE__ */ __name((values) => Object.keys(values).map((key) => ({ key, value: values[key] })), "filters");
const asArguments = /* @__PURE__ */ __name((parameters) => ({
  sourcefilters: filters(parameters.source),
  targetfilters: filters(parameters.target),
  rules: JSON.stringify(parameters.rules)
}), "asArguments");
const listMigrations = /* @__PURE__ */ __name((search, state, page, perpage) => call("list", { search, state, page, perpage }), "listMigrations");
const getOptions = /* @__PURE__ */ __name((search = "", profileid = 0) => call("options", { search, profileid }), "getOptions");
const checkMigration = /* @__PURE__ */ __name((parameters) => call("check", { profileid: parameters.profileid, ...asArguments(parameters) }), "checkMigration");
const createMigration = /* @__PURE__ */ __name((parameters) => call("create", { profileid: parameters.profileid, ...asArguments(parameters) }), "createMigration");
const getMigration = /* @__PURE__ */ __name((id) => call("get", { id }), "getMigration");
const getFiles = /* @__PURE__ */ __name((id, bucket, search, page, perpage) => call("files", { id, bucket, search, page, perpage }), "getFiles");
const getFolders = /* @__PURE__ */ __name((id, search, page, perpage) => call("folders", { id, search, page, perpage }), "getFolders");
const reconfigureMigration = /* @__PURE__ */ __name((id, digest, parameters) => call("reconfigure", { id, digest, ...asArguments(parameters) }), "reconfigureMigration");
const selectFiles = /* @__PURE__ */ __name((id, digest, action, visible = [], included = []) => call("select", { id, digest, action, visible, included }), "selectFiles");
const selectFolder = /* @__PURE__ */ __name((id, digest, folder, included) => call("select", {
  id,
  digest,
  action: "group",
  groupcourseid: folder.sourcecourseid,
  groupcmid: folder.sourcecmid,
  groupincluded: included ? 1 : 0
}), "selectFolder");
const executeMigration = /* @__PURE__ */ __name((id, digest) => call("execute", { id, digest }), "executeMigration");
const cancelRun = /* @__PURE__ */ __name((id, runid) => call("cancel", { id, runid }), "cancelRun");
const deleteMigrations = /* @__PURE__ */ __name((ids) => call("delete", { ids }), "deleteMigrations");
export {
  cancelRun,
  checkMigration,
  createMigration,
  deleteMigrations,
  executeMigration,
  getFiles,
  getFolders,
  getMigration,
  getOptions,
  listMigrations,
  reconfigureMigration,
  selectFiles,
  selectFolder
};
//# sourceMappingURL=api.dev.js.map
