var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * One migration as the screen shows it: it is read, kept in step with the server while it runs, and replaced by what every change
 * answers, because each answer carries the digest that the next change has to send.
 *
 * @module     local_filebank/migration/hooks/useMigration
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useCallback, useEffect, useRef, useState } from "react";
import { describeError, isStale } from "../lib/errors";
import { getMigration } from "../services/api";
const POLL_MILLISECONDS = 3e3;
const useMigration = /* @__PURE__ */ __name((id) => {
  const [migration, setMigration] = useState(null);
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");
  const [stale, setStale] = useState(false);
  const current = useRef(id);
  const fail = useCallback((problem) => {
    setStale(isStale(problem));
    setError(describeError(problem));
  }, []);
  const reload = useCallback(async () => {
    try {
      const read = await getMigration(current.current);
      setMigration(read);
      setStale(false);
      setError("");
    } catch (problem) {
      fail(problem);
    } finally {
      setLoading(false);
    }
  }, [fail]);
  useEffect(() => {
    current.current = id;
    setMigration(null);
    setLoading(true);
    setError("");
    setStale(false);
    void reload();
  }, [id, reload]);
  const running = migration?.run?.active === true;
  useEffect(() => {
    if (!running) {
      return void 0;
    }
    const timer = window.setInterval(() => void reload(), POLL_MILLISECONDS);
    return () => window.clearInterval(timer);
  }, [running, reload]);
  const apply = useCallback(async (change, pick) => {
    setBusy(true);
    setError("");
    try {
      const answer = await change();
      setMigration(pick ? pick(answer) : answer);
      setStale(false);
      return answer;
    } catch (problem) {
      fail(problem);
      return null;
    } finally {
      setBusy(false);
    }
  }, [fail]);
  return { migration, loading, busy, error, stale, clearError: /* @__PURE__ */ __name(() => setError(""), "clearError"), reload, apply };
}, "useMigration");
export {
  useMigration
};
//# sourceMappingURL=useMigration.dev.js.map
