/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The types that the parts of the migration screens share: what the server sends and what the person edits.
 *
 * @module     local_filebank/migration/types
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** Looks a text of the page up, putting the value in the place that the text marks. */
export type Text = (name: string, value?: string | number | Record<string, string | number>) => string;

/** What the page gives the application to start from. */
export interface MigrationProps {
    /** The migration to open, or 0 for the list. */
    planid: number;
    /** Where the list and the migrations live, without the query. */
    pageurl: string;
    pagesizes: number[];
    strings: Record<string, string>;
}

export interface Summary {
    files: number;
    tocopy: number;
    tooverwrite: number;
    existing: number;
    skipped: number;
    included: number;
    folders: number;
    sourcecourses: number;
    targetcourses: number;
    /** Files that already have a different file in the destination, whatever is decided about them. */
    different: number;
    haswork: boolean;
    notes: Array<{kind: string; count: number; label: string}>;
}

export interface FilterValue {
    key: string;
    label: string;
    value: string;
}

export interface RunState {
    id: number;
    state: string;
    statelabel: string;
    done: number;
    total: number;
    active: boolean;
    cancancel: boolean;
}

export interface Migration {
    id: number;
    uuid: string;
    profileid: number;
    profile: string;
    state: string;
    statelabel: string;
    digest: string;
    timecreated: number;
    datelabel: string;
    editable: boolean;
    /** The profile changed after the migration was worked out, so it has to be worked out again before it can start. */
    outdated: boolean;
    canexecute: boolean;
    summary: Summary;
    source: string;
    target: string;
    filters: {source: FilterValue[]; target: FilterValue[]};
    rules: string;
    run?: RunState;
}

export interface ListedMigration {
    id: number;
    profile: string;
    source: string;
    target: string;
    state: string;
    statelabel: string;
    timecreated: number;
    datelabel: string;
    files: number;
    towrite: number;
    done: number;
    candelete: boolean;
}

export interface MigrationPage {
    total: number;
    counts: Array<{state: string; count: number}>;
    migrations: ListedMigration[];
}

export interface Dimension {
    key: string;
    label: string;
}

export interface ProfileOption {
    id: number;
    name: string;
    dimensions: Dimension[];
    rules: string;
}

export interface Options {
    profiles: ProfileOption[];
    modules: Array<{value: string; label: string}>;
    /** The last migrations of the chosen profile, to take their folders from. */
    previous: Array<{id: number; label: string}>;
}

export interface MigrationFile {
    id: number;
    filename: string;
    filepath: string;
    sourcecourse: string;
    sourcefolder: string;
    targetcourse: string;
    targetfolder: string;
    decision: string;
    decisionlabel: string;
    included: boolean;
    settable: boolean;
    state: string;
    statelabel: string;
    result: string;
    author: string;
    license: string;
}

export type Bucket = '' | 'copy' | 'existing' | 'left';

export interface FilePage {
    counts: Array<{bucket: string; count: number}>;
    total: number;
    files: MigrationFile[];
}

export interface MigrationFolder {
    sourcecourseid: number;
    sourcecmid: number;
    sourcecourse: string;
    sourcefolder: string;
    targetcourse: string;
    targetfolder: string;
    total: number;
    included: number;
}

export interface FolderPage {
    total: number;
    folders: MigrationFolder[];
}

/** One row of the correspondences between a folder of the source and a folder of the destination. */
export interface Correspondence {
    sourcetype: string;
    sourcefield: string;
    sourcemethod: string;
    sourcevalue: string;
    targetfield: string;
    targetmethod: string;
    targetvalue: string;
}

export interface SectionPair {
    source: string;
    target: string;
}

/** What a person edits about how a migration reads and writes folders. */
export interface Rules {
    correspondences: Correspondence[];
    sectionstrategy: string;
    sectionmap: SectionPair[];
    duplicatepolicy: string;
    missingtarget: string;
    conflictpolicy: string;
}

/** The parameters of a migration: the profile, the courses of both sides and the rules. */
export interface Parameters {
    profileid: number;
    source: Record<string, string>;
    target: Record<string, string>;
    rules: Rules;
}
