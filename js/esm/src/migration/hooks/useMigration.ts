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
import {useCallback, useEffect, useRef, useState} from 'react';
import {describeError, isStale} from '../lib/errors';
import {getMigration} from '../services/api';
import type {Migration} from '../types';

/** How long to wait between two readings of a migration that is running. */
const POLL_MILLISECONDS = 3000;

export interface MigrationState {
    migration: Migration | null;
    /** The first reading is on its way. */
    loading: boolean;
    /** A change is being saved. */
    busy: boolean;
    /** What went wrong with the last reading or change. */
    error: string;
    /** The migration changed somewhere else, so what is on screen is not what the server has. */
    stale: boolean;
    clearError: () => void;
    reload: () => Promise<void>;
    /**
     * Run a change and keep what it answers.
     *
     * @param change The call; it answers the migration or something that holds it.
     * @param pick Takes the migration out of the answer; the answer itself when it is not given.
     * @returns The answer, or null when the change failed.
     */
    apply: <T>(change: () => Promise<T>, pick?: (answer: T) => Migration) => Promise<T | null>;
}

/**
 * Keep a migration.
 *
 * @param id The migration.
 * @returns The migration and what can be done with it.
 */
export const useMigration = (id: number): MigrationState => {
    const [migration, setMigration] = useState<Migration | null>(null);
    const [loading, setLoading] = useState(true);
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState('');
    const [stale, setStale] = useState(false);
    const current = useRef(id);

    const fail = useCallback((problem: unknown) => {
        setStale(isStale(problem));
        setError(describeError(problem));
    }, []);

    const reload = useCallback(async() => {
        try {
            const read = await getMigration(current.current);
            setMigration(read);
            setStale(false);
            setError('');
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
        setError('');
        setStale(false);
        void reload();
    }, [id, reload]);

    const running = migration?.run?.active === true;
    useEffect(() => {
        if (!running) {
            return undefined;
        }
        const timer = window.setInterval(() => void reload(), POLL_MILLISECONDS);
        return () => window.clearInterval(timer);
    }, [running, reload]);

    const apply = useCallback(async<T,>(change: () => Promise<T>, pick?: (answer: T) => Migration): Promise<T | null> => {
        setBusy(true);
        setError('');
        try {
            const answer = await change();
            setMigration(pick ? pick(answer) : answer as unknown as Migration);
            setStale(false);
            return answer;
        } catch (problem) {
            fail(problem);
            return null;
        } finally {
            setBusy(false);
        }
    }, [fail]);

    return {migration, loading, busy, error, stale, clearError: () => setError(''), reload, apply};
};
