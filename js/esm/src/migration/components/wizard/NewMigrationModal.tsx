/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The window that starts a migration: the person chooses the parameters, sees what a migration with them would do, and only then
 * creates it. Nothing is stored until then, so parameters that find nothing to migrate are changed here and not in a new migration.
 *
 * @module     local_filebank/migration/components/wizard/NewMigrationModal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useState} from 'react';
import {describeError} from '../../lib/errors';
import {keyOf, parametersFor, problemsOf, type Problem} from '../../lib/parameters';
import {checkMigration, createMigration} from '../../services/api';
import type {Options, Parameters, Summary, Text} from '../../types';
import CheckResult from '../parameters/CheckResult';
import ParametersEditor from '../parameters/ParametersEditor';
import Alert from '../ui/Alert';
import Icon from '../ui/Icon';
import Modal from '../ui/Modal';

interface NewMigrationModalProps {
    open: boolean;
    options: Options | null;
    /** The parameters to start from, such as the ones of another migration; the first profile when it is not given. */
    initial: Parameters | null;
    onClose: () => void;
    onCreated: (id: number) => void;
    text: Text;
}

type Step = 'setup' | 'check';

const NewMigrationModal = ({open, options, initial, onClose, onCreated, text}: NewMigrationModalProps) => {
    const [parameters, setParameters] = useState<Parameters | null>(null);
    const [step, setStep] = useState<Step>('setup');
    const [problems, setProblems] = useState<Problem[]>([]);
    const [summary, setSummary] = useState<Summary | null>(null);
    const [checking, setChecking] = useState(false);
    const [creating, setCreating] = useState(false);
    const [error, setError] = useState('');

    useEffect(() => {
        if (!open || !options) {
            return;
        }
        setParameters(initial ?? (options.profiles[0] ? parametersFor(options.profiles[0]) : null));
        setStep('setup');
        setProblems([]);
        setSummary(null);
        setError('');
    }, [open, options, initial]);

    if (!options || !parameters) {
        return <Modal open={open} size="large" title={text('wizard_title')} onClose={onClose} closeLabel={text('close')}>
            {open && options && options.profiles.length === 0
                ? <Alert tone="warning">{text('wizard_no_profiles')}</Alert>
                : <p className="fbm-muted">{text('loading')}</p>}
        </Modal>;
    }

    const dimensions = options.profiles.find((item) => item.id === parameters.profileid)?.dimensions.length ?? 0;

    const check = async() => {
        const found = problemsOf(parameters, dimensions);
        setProblems(found);
        if (found.length > 0) {
            return;
        }
        setStep('check');
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

    const create = async() => {
        setCreating(true);
        setError('');
        try {
            const created = await createMigration(parameters);
            onCreated(created.id);
        } catch (problem) {
            setError(describeError(problem));
            setCreating(false);
        }
    };

    const canCreate = summary !== null && (summary.files > 0 || summary.openissues > 0);

    return <Modal
        open={open}
        size="large"
        busy={creating}
        title={<><Icon name="wand-magic-sparkles" gap />{text('wizard_title')}</>}
        onClose={onClose}
        closeLabel={text('close')}
        footer={step === 'setup'
            ? <>
                <button type="button" className="fbm-btn" onClick={onClose}>{text('cancel')}</button>
                <button type="button" className="fbm-btn fbm-btn-primary" onClick={() => void check()} disabled={options.profiles.length === 0}>
                    {text('wizard_check')}<Icon name="arrow-right" />
                </button>
            </>
            : <>
                <button type="button" className="fbm-btn" onClick={() => setStep('setup')} disabled={creating}>
                    <Icon name="arrow-left" gap />{text('wizard_change')}
                </button>
                <button type="button" className="fbm-btn fbm-btn-primary" onClick={() => void create()} disabled={!canCreate || creating}>
                    {creating ? <Icon name="spinner" gap spin /> : <Icon name="check" gap />}{text('wizard_create')}
                </button>
            </>}
    >
        <ol className="fbm-steps" aria-label={text('wizard_steps')}>
            <li className={step === 'setup' ? 'is-current' : 'is-done'}><span>1</span>{text('wizard_step_setup')}</li>
            <li className={step === 'check' ? 'is-current' : ''}><span>2</span>{text('wizard_step_check')}</li>
        </ol>
        {step === 'setup'
            ? <ParametersEditor options={options} parameters={parameters} onChange={setParameters} text={text} problems={problems} />
            : <CheckResult summary={summary} loading={checking} error={error} text={text} />}
        {step === 'setup' && error !== '' && <Alert tone="danger" live>{error}</Alert>}
        {step === 'check' && summary !== null && !canCreate && <p className="fbm-muted fbm-wizard-hint">{text('wizard_change_hint')}</p>}
    </Modal>;
};

export default NewMigrationModal;
