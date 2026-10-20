/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The parameters of a migration: where they start from, the steps that ask for them and when each step is complete.
 *
 * @module     local_filebank/migration/lib/parameters
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {Correspondence, Migration, Parameters, ProfileOption, Rules} from '../types';

/** The most correspondences a migration can have. */
export const MAX_CORRESPONDENCES = 20;

/** The most pairs of sections a migration can map. */
export const MAX_SECTION_PAIRS = 200;

/** The steps that ask for the parameters of a migration, in order. */
export type StepKey = 'profile' | 'courses' | 'folders' | 'sections' | 'policies' | 'check';

export const STEP_KEYS: StepKey[] = ['profile', 'courses', 'folders', 'sections', 'policies', 'check'];

/**
 * A correspondence that reads a folder by name and writes the folder with the same name.
 *
 * @returns The new row.
 */
export const sameNameCorrespondence = (): Correspondence => ({
    sourcetype: 'folder',
    sourcefield: 'name',
    sourcemethod: 'pattern',
    sourcevalue: '~^(?<name>.+)$~',
    targetfield: 'name',
    targetmethod: 'template',
    // The template of the destination, in which ${name} is the name that the pattern of the source captured.
    // eslint-disable-next-line no-template-curly-in-string
    targetvalue: '${name}',
});

/**
 * A correspondence that is yet to be filled in.
 *
 * @returns The new row, which finds a folder by its exact name on both sides.
 */
export const emptyCorrespondence = (): Correspondence => ({
    sourcetype: 'folder',
    sourcefield: 'name',
    sourcemethod: 'equals',
    sourcevalue: '',
    targetfield: 'name',
    targetmethod: 'equals',
    targetvalue: '',
});

/**
 * The rules a profile proposes.
 *
 * @param option The profile.
 * @returns The rules, with the correspondences that the profile has.
 */
export const profileRules = (option: ProfileOption): Rules => JSON.parse(option.rules) as Rules;

/**
 * The parameters to start a new migration from a profile. No folder is chosen for the person: the correspondences start empty.
 *
 * @param option The profile.
 * @returns The parameters, with no course filter.
 */
export const parametersFor = (option: ProfileOption): Parameters => ({
    profileid: option.id,
    source: {},
    target: {},
    rules: {...profileRules(option), correspondences: []},
});

/**
 * The parameters that a migration has.
 *
 * @param migration The migration.
 * @returns The parameters, to start another migration from them or to change them.
 */
export const parametersOf = (migration: Migration): Parameters => {
    const values = (list: Migration['filters']['source']) => {
        const map: Record<string, string> = {};
        list.forEach(filter => {
            if (filter.value !== '') {
                map[filter.key] = filter.value;
            }
        });
        return map;
    };
    return {
        profileid: migration.profileid,
        source: values(migration.filters.source),
        target: values(migration.filters.target),
        rules: JSON.parse(migration.rules) as Rules,
    };
};

/** What can be missing from the parameters of a migration. */
export type Problem = 'profile' | 'courses' | 'correspondence' | 'sections';

/**
 * Whether a value is something a correspondence can use.
 *
 * @param row The correspondence.
 * @returns True when both sides have a value.
 */
const complete = (row: Correspondence): boolean => row.sourcevalue.trim() !== '' && row.targetvalue.trim() !== '';

/**
 * What is missing from the parameters before a migration can be worked out.
 *
 * @param parameters The parameters.
 * @param dimensions How many filters the profile has, so a profile without any does not ask for a course.
 * @returns The problems, none when they are complete.
 */
export const problemsOf = (parameters: Parameters, dimensions: number): Problem[] => {
    const problems: Problem[] = [];
    if (parameters.profileid <= 0) {
        problems.push('profile');
    }
    if (dimensions > 0 && Object.keys(parameters.source).length + Object.keys(parameters.target).length === 0) {
        problems.push('courses');
    }
    if (parameters.rules.correspondences.length === 0 || !parameters.rules.correspondences.every(complete)) {
        problems.push('correspondence');
    }
    if (parameters.rules.sectionstrategy === 'mapping') {
        const pairs = parameters.rules.sectionmap;
        const valid = pairs.length > 0 && pairs.every(pair => /^[1-9][0-9]*$/.test(pair.source) && /^[1-9][0-9]*$/.test(pair.target));
        if (!valid) {
            problems.push('sections');
        }
    }
    return problems;
};

/** The problem that each step is responsible for. */
const STEP_PROBLEM: Partial<Record<StepKey, Problem>> = {
    profile: 'profile',
    courses: 'courses',
    folders: 'correspondence',
    sections: 'sections',
};

/**
 * What is missing in one step.
 *
 * @param step The step.
 * @param parameters The parameters.
 * @param dimensions How many filters the profile has.
 * @returns The problems that belong to the step.
 */
export const stepProblems = (step: StepKey, parameters: Parameters, dimensions: number): Problem[] => {
    const own = STEP_PROBLEM[step];
    return own ? problemsOf(parameters, dimensions).filter(problem => problem === own) : [];
};

/**
 * A key that changes when the parameters do, to know whether a check is still the check of what is on screen.
 *
 * @param parameters The parameters.
 * @returns The key.
 */
export const keyOf = (parameters: Parameters): string => JSON.stringify(parameters);
