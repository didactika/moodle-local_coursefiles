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
import {fetchOne} from '@moodle/lms/core/ajax';
import type {
    Bucket, DecisionPage, FilePage, FolderPage, Migration, MigrationPage, Options, Parameters, Summary,
} from '../types';

const call = <T>(name: string, args: Record<string, unknown>): Promise<T> => fetchOne<T>({
    methodname: 'local_filebank_migration_' + name,
    args,
});

/**
 * The filters of one side as the web service takes them.
 *
 * @param values The value of each filter by key.
 * @returns The list of keys and values.
 */
const filters = (values: Record<string, string>) => Object.keys(values).map(key => ({key, value: values[key]}));

/**
 * The parameters as the web services take them.
 *
 * @param parameters What the person chose.
 * @returns The arguments that every call that works a migration out has in common.
 */
const asArguments = (parameters: Parameters) => ({
    sourcefilters: filters(parameters.source),
    targetfilters: filters(parameters.target),
    rules: JSON.stringify(parameters.rules),
});

/** Read a page of the migrations of the site. */
export const listMigrations = (search: string, state: string, page: number, perpage: number): Promise<MigrationPage> =>
    call('list', {search, state, page, perpage});

/** Read what a new migration can be asked to do. */
export const getOptions = (): Promise<Options> => call('options', {});

/** Count what a migration with these parameters would do, without storing anything. */
export const checkMigration = (parameters: Parameters): Promise<Summary> =>
    call('check', {profileid: parameters.profileid, ...asArguments(parameters)});

/** Work a migration out and store it. */
export const createMigration = (parameters: Parameters): Promise<{id: number}> =>
    call('create', {profileid: parameters.profileid, ...asArguments(parameters)});

/** Read one migration. */
export const getMigration = (id: number): Promise<Migration> => call('get', {id});

/** Read a page of what waits for a decision. */
export const getDecisions = (id: number, kind: string, nature: string, page: number, perpage: number): Promise<DecisionPage> =>
    call('decisions', {id, kind, nature, page, perpage});

/** Read a page of the files of a migration. */
export const getFiles = (id: number, bucket: Bucket, search: string, page: number, perpage: number): Promise<FilePage> =>
    call('files', {id, bucket, search, page, perpage});

/** Read a page of the folders of a migration. */
export const getFolders = (id: number, search: string, page: number, perpage: number): Promise<FolderPage> =>
    call('folders', {id, search, page, perpage});

/** Settle what waits for a decision: some of it, or all of one nature when no ids are given. */
export const resolveDecisions = (
    id: number, digest: string, kind: string, nature: string, ids: number[], choice: string,
): Promise<{changed: number; migration: Migration}> => call('resolve', {id, digest, kind, nature, ids, choice});

/** Change the parameters of a migration that has not run. */
export const reconfigureMigration = (id: number, digest: string, parameters: Parameters): Promise<Migration> =>
    call('reconfigure', {id, digest, ...asArguments(parameters)});

/** Put the files of a page in or out of the migration, or all of them, or none. */
export const selectFiles = (
    id: number, digest: string, action: 'page' | 'all' | 'none', visible: number[] = [], included: number[] = [],
): Promise<Migration> => call('select', {id, digest, action, visible, included});

/** Put every file of a folder in or out of the migration. */
export const selectFolder = (
    id: number, digest: string, folder: {sourcecourseid: number; sourcecmid: number}, included: boolean,
): Promise<Migration> => call('select', {
    id, digest, action: 'group', groupcourseid: folder.sourcecourseid, groupcmid: folder.sourcecmid,
    groupincluded: included ? 1 : 0,
});

/** Queue the run of a migration. */
export const executeMigration = (id: number, digest: string): Promise<Migration> => call('execute', {id, digest});

/** Stop a run. */
export const cancelRun = (id: number, runid: number): Promise<Migration> => call('cancel', {id, runid});

/** Delete migrations from the history. */
export const deleteMigrations = (ids: number[]): Promise<{deleted: number}> => call('delete', {ids});
