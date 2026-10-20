var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Lists of server pages that change page in place: the arrows and the size of a page fetch the next page and replace only the
 * region of the list (its table and its pager), keeping its height so a shorter last page does not make the page jump.
 *
 * @module local_filebank/local/admin/lists
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const swapList = /* @__PURE__ */ __name(async (origin, url, keep = true) => {
  const region = origin.closest("[data-filebank-list]");
  if (!region) {
    window.location.assign(url);
    return;
  }
  const table = region.querySelector(".table-responsive");
  const height = table ? table.offsetHeight : 0;
  region.setAttribute("aria-busy", "true");
  try {
    const response = await fetch(url, { credentials: "same-origin" });
    const page = new DOMParser().parseFromString(await response.text(), "text/html");
    const next = page.querySelector(`[data-filebank-list="${region.dataset.filebankList}"]`);
    if (!response.ok || !next) {
      window.location.assign(url);
      return;
    }
    const pager = region.querySelector(".filebank-pagination");
    const nextPager = next.querySelector(".filebank-pagination");
    if (pager && nextPager) {
      const select = pager.querySelector("select");
      const nextSelect = nextPager.querySelector("select");
      if (select && nextSelect) {
        select.innerHTML = nextSelect.innerHTML;
        select.value = nextSelect.value;
        select.dataset.url = nextSelect.dataset.url || "";
      }
      const pages = pager.querySelector(".fbm-listbar-pages");
      const nextPages = nextPager.querySelector(".fbm-listbar-pages");
      if (pages && nextPages) {
        pages.replaceWith(document.importNode(nextPages, true));
      }
      nextPager.remove();
      Array.from(region.childNodes).filter((node) => node !== pager).forEach((node) => node.remove());
      Array.from(next.childNodes).forEach((node) => region.insertBefore(document.importNode(node, true), pager));
    } else {
      region.replaceChildren(...Array.from(next.childNodes, (node) => document.importNode(node, true)));
    }
    const fresh = region.querySelector(".table-responsive");
    if (fresh) {
      fresh.style.minHeight = keep && height > 0 ? `${height}px` : "";
    }
    window.history.replaceState(window.history.state, "", url);
    region.dispatchEvent(new CustomEvent("filebank:listchanged", { bubbles: true }));
  } catch (problem) {
    window.location.assign(url);
  } finally {
    region.removeAttribute("aria-busy");
  }
}, "swapList");
const registerLists = /* @__PURE__ */ __name(() => {
  if (document.body.dataset.filebankLists) {
    return;
  }
  document.body.dataset.filebankLists = "true";
  document.addEventListener("click", (event) => {
    const link = event.target instanceof Element ? event.target.closest("[data-filebank-list] .filebank-pagination a[href]") : null;
    if (link) {
      event.preventDefault();
      void swapList(link, link.href);
    }
  });
}, "registerLists");
export {
  registerLists,
  swapList
};
//# sourceMappingURL=lists.dev.js.map
