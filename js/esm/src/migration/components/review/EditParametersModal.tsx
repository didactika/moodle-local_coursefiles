/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The window that changes the parameters of a migration that has not run. The migration is worked out again where it is, so it
 * keeps its number, its date and what was already decided; no other migration is created.
 *
 * @module     local_filebank/migration/components/review/EditParametersModal
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useState} from 'react';
import {problemsOf, type Problem} from '../../lib/parameters';
import type {Options, Parameters, Text} from '../../types';
import ParametersEditor from '../parameters/ParametersEditor';
import Alert from '../ui/Alert';
import Icon from '../ui/Icon';
import Modal from '../ui/Modal';

interface EditParametersModalProps {
    open: boolean;
    options: Options | null;
    initial: Parameters;
    text: Text;
    busy: boolean;
    error: string;
    onSave: (parameters: Parameters) => void;
    onClose: () => void;
}

const EditParametersModal = ({open, options, initial, text, busy, error, onSave, onClose}: EditParametersModalProps) => {
    const [parameters, setParameters] = useState(initial);
    const [problems, setProblems] = useState<Problem[]>([]);

    useEffect(() => {
        if (open) {
            setParameters(initial);
            setProblems([]);
        }
    }, [open, initial]);

    const dimensions = options?.profiles.find((item) => item.id === parameters.profileid)?.dimensions.length ?? 0;

    const save = () => {
        const found = problemsOf(parameters, dimensions);
        setProblems(found);
        if (found.length === 0) {
            onSave(parameters);
        }
    };

    return <Modal
        open={open}
        size="large"
        busy={busy}
        title={<><Icon name="sliders" gap />{text('edit_title')}</>}
        onClose={onClose}
        closeLabel={text('close')}
        footer={<>
            <button type="button" className="fbm-btn" onClick={onClose} disabled={busy}>{text('cancel')}</button>
            <button type="button" className="fbm-btn fbm-btn-primary" onClick={save} disabled={busy || !options}>
                {busy ? <Icon name="spinner" gap spin /> : <Icon name="check" gap />}{text('edit_save')}
            </button>
        </>}
    >
        <Alert tone="info">{text('edit_hint')}</Alert>
        {error !== '' && <Alert tone="danger" live>{error}</Alert>}
        {options
            ? <ParametersEditor options={options} parameters={parameters} onChange={setParameters} text={text} lockProfile problems={problems} />
            : <p className="fbm-muted">{text('loading')}</p>}
    </Modal>;
};

export default EditParametersModal;
