/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The page that starts a migration, or changes the parameters of one that has not run: the steps are listed on the left, the open
 * one is on the right, and what a migration with the chosen parameters would do is counted in the last one before anything is
 * stored. It is a page and not a window, so nothing that was typed is lost by clicking away; leaving it asks first.
 *
 * @module     local_filebank/migration/components/wizard/MigrationWizard
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useRef, useState} from 'react';
import {describeError} from '../../lib/errors';
import {
    keyOf, parametersFor, parametersOf, STEP_KEYS, stepProblems, type Problem, type StepKey,
} from '../../lib/parameters';
import {addToast} from '../../lib/toast';
import {checkMigration, createMigration, getMigration, reconfigureMigration} from '../../services/api';
import type {Migration, Options, Parameters, Summary, Text} from '../../types';
import CheckResult from '../parameters/CheckResult';
import ParametersEditor from '../parameters/ParametersEditor';
import Alert from '../ui/Alert';
import ConfirmModal from '../ui/ConfirmModal';
import Icon from '../ui/Icon';
import PageHeader from '../ui/PageHeader';
import Skeleton from '../ui/Skeleton';
import Stepper from './Stepper';

interface MigrationWizardProps {
    /** A new migration, or the changes of one that has not run. */
    mode: 'new' | 'edit';
    options: Options | null;
    /** The migration to change, or the one a new migration starts from; 0 when there is none. */
    migrationId: number;
    onClose: () => void;
    /** The migration was created or changed. */
    onDone: (id: number) => void;
    text: Text;
}

const STEP_ICONS: Record<StepKey, string> = {
    profile: 'layer-group',
    courses: 'graduation-cap',
    folders: 'folder-tree',
    sections: 'list-ol',
    policies: 'sliders',
    check: 'clipboard-check',
};

const MigrationWizard = ({mode, options, migrationId, onClose, onDone, text}: MigrationWizardProps) => {
    const [parameters, setParameters] = useState<Parameters | null>(null);
    const [source, setSource] = useState<Migration | null>(null);
    const [step, setStep] = useState(0);
    // The parameters of a migration that exists are complete, so every one of its steps can be opened.
    const [reached, setReached] = useState(mode === 'edit' ? STEP_KEYS.length - 1 : 0);
    const [problems, setProblems] = useState<Problem[]>([]);
    const [summary, setSummary] = useState<Summary | null>(null);
    const [checking, setChecking] = useState(false);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState('');
    const [leaving, setLeaving] = useState(false);
    const initial = useRef('');
    const steps = STEP_KEYS;

    // What the page starts from: the migration that is changed or copied, or the first profile.
    useEffect(() => {
        if (!options) {
            return undefined;
        }
        let current = true;
        const start = (found: Parameters | null, migration: Migration | null) => {
            if (!current) {
                return;
            }
            const base = found ?? (options.profiles[0] ? parametersFor(options.profiles[0]) : null);
            initial.current = base ? keyOf(base) : '';
            setParameters(base);
            setSource(migration);
        };
        if (migrationId > 0) {
            getMigration(migrationId).then((migration) => start(parametersOf(migration), migration)).catch((problem: unknown) => {
                if (current) {
                    setError(describeError(problem));
                }
            });
        } else {
            start(null, null);
        }
        return () => {
            current = false;
        };
    }, [options, migrationId]);

    // Leaving the page with something that was typed asks first.
    const dirty = parameters !== null && keyOf(parameters) !== initial.current;
    useEffect(() => {
        if (!dirty) {
            return undefined;
        }
        const warn = (event: BeforeUnloadEvent) => {
            event.preventDefault();
        };
        window.addEventListener('beforeunload', warn);
        return () => window.removeEventListener('beforeunload', warn);
    }, [dirty]);

    const heading = mode === 'edit' ? text('edit_title') : text('wizard_title');
    const back = <button type="button" className="fbm-back" onClick={() => (dirty ? setLeaving(true) : onClose())}>
        <Icon name="arrow-left" gap />{text(mode === 'edit' ? 'back_to_migration' : 'back_to_list')}
    </button>;

    if (!options || !parameters) {
        return <div className="fbm-wizard">
            <PageHeader title={heading} back={back} />
            {error !== '' && <Alert tone="danger" live>{error}</Alert>}
            {options && options.profiles.length === 0 && <Alert tone="warning">{text('wizard_no_profiles')}</Alert>}
            {error === '' && (!options || options.profiles.length > 0) && <Skeleton rows={5} label={text('loading')} />}
        </div>;
    }

    const dimensions = options.profiles.find((item) => item.id === parameters.profileid)?.dimensions.length ?? 0;
    const key = steps[step];
    const last = step === steps.length - 1;

    const change = (next: Parameters) => {
        setParameters(next);
        setSummary(null);
    };

    const runCheck = async() => {
        setChecking(true);
        setSummary(null);
        setError('');
        const asked = keyOf(parameters);
        try {
            const result = await checkMigration(parameters);
            if (asked === keyOf(parameters)) {
                setSummary(result);
            }
        } catch (problem) {
            setError(describeError(problem));
        } finally {
            setChecking(false);
        }
    };

    const go = (index: number) => {
        setStep(index);
        setProblems([]);
        setError('');
        setReached(Math.max(reached, index));
        if (steps[index] === 'check') {
            void runCheck();
        }
    };

    const next = () => {
        const found = stepProblems(key, parameters, dimensions);
        setProblems(found);
        if (found.length === 0) {
            go(step + 1);
        }
    };

    const save = async() => {
        setSaving(true);
        setError('');
        try {
            if (mode === 'edit' && source) {
                await reconfigureMigration(source.id, source.digest, parameters);
                void addToast(text('edit_saved'));
                initial.current = keyOf(parameters);
                onDone(source.id);
            } else {
                const created = await createMigration(parameters);
                initial.current = keyOf(parameters);
                onDone(created.id);
            }
        } catch (problem) {
            setError(describeError(problem));
            setSaving(false);
        }
    };

    const canSave = summary !== null && (mode === 'edit' || summary.files > 0 || summary.openissues > 0);

    return <div className="fbm-wizard" data-region="migration-wizard" data-mode={mode}>
        <PageHeader
            title={mode === 'edit' ? text('edit_title_n', {n: source?.id ?? migrationId}) : heading}
            subtitle={text(mode === 'edit' ? 'edit_hint' : 'wizard_subtitle')}
            back={back}
        />
        <div className="fbm-wizard-body">
            <Stepper steps={steps} current={step} reached={reached} onGo={go} text={text} />
            <section className="fbm-wizard-panel" aria-live="polite">
                <header className="fbm-wizard-head">
                    <span className="fbm-wizard-icon"><Icon name={STEP_ICONS[key]} /></span>
                    <div>
                        <h3 className="fbm-h3">{text('step_' + key)}</h3>
                        <p className="fbm-muted fbm-wizard-help">{text('step_' + key + '_help')}</p>
                    </div>
                </header>
                {error !== '' && <Alert tone="danger" live>{error}</Alert>}
                {key === 'check'
                    ? <CheckResult summary={summary} loading={checking} error="" text={text} />
                    : <ParametersEditor
                        step={key}
                        options={options}
                        parameters={parameters}
                        onChange={change}
                        text={text}
                        lockProfile={mode === 'edit'}
                        problems={problems}
                    />}
                {key === 'check' && summary !== null && !canSave && <p className="fbm-muted fbm-wizard-hint">{text('wizard_change_hint')}</p>}
                <footer className="fbm-wizard-foot">
                    <button type="button" className="fbm-btn" onClick={() => go(step - 1)} disabled={step === 0 || saving}>
                        <Icon name="arrow-left" gap />{text('wizard_previous')}
                    </button>
                    {last
                        ? <button
                            type="button"
                            className="fbm-btn fbm-btn-primary"
                            onClick={() => void save()}
                            disabled={!canSave || saving}
                            data-action="save-migration"
                        >
                            {saving ? <Icon name="spinner" gap spin /> : <Icon name="check" gap />}
                            {text(mode === 'edit' ? 'edit_save' : 'wizard_create')}
                        </button>
                        : <button type="button" className="fbm-btn fbm-btn-primary" onClick={next} data-action="next-step">
                            {text(steps[step + 1] === 'check' ? 'wizard_check' : 'wizard_next')}<Icon name="arrow-right" />
                        </button>}
                </footer>
            </section>
        </div>
        <ConfirmModal
            open={leaving}
            tone="danger"
            icon="triangle-exclamation"
            title={text('discard_title')}
            confirmLabel={text('discard')}
            cancelLabel={text('keep_editing')}
            onConfirm={() => {
                initial.current = keyOf(parameters);
                onClose();
            }}
            onCancel={() => setLeaving(false)}
        >
            <p>{text('discard_text')}</p>
        </ConfirmModal>
    </div>;
};

export default MigrationWizard;
