/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The steps of the page that starts a migration, listed down the left side: the ones that are done, the one that is open and the
 * ones that are still to come. A step that was reached can be opened again; one that was not cannot be skipped to.
 *
 * @module     local_filebank/migration/components/wizard/Stepper
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import type {StepKey} from '../../lib/parameters';
import type {Text} from '../../types';
import Icon from '../ui/Icon';

interface StepperProps {
    steps: StepKey[];
    current: number;
    /** The furthest step the person has reached. */
    reached: number;
    onGo: (index: number) => void;
    text: Text;
}

const Stepper = ({steps, current, reached, onGo, text}: StepperProps) => <nav className="fbm-stepper" aria-label={text('wizard_steps')}>
    <ol className="fbm-stepper-list">
        {steps.map((step, index) => {
            const state = index === current ? 'is-current' : index < current || index <= reached ? 'is-done' : 'is-todo';
            const open = index <= reached;
            return <li key={step} className={`fbm-stepper-item ${state}`}>
                <button
                    type="button"
                    className="fbm-stepper-button"
                    disabled={!open}
                    aria-current={index === current ? 'step' : undefined}
                    onClick={() => onGo(index)}
                    data-step={step}
                >
                    <span className="fbm-stepper-dot">{state === 'is-done' ? <Icon name="check" /> : index + 1}</span>
                    <span className="fbm-stepper-text">
                        <span className="fbm-stepper-title">{text('step_' + step)}</span>
                        <span className="fbm-stepper-hint">{text('step_' + step + '_short')}</span>
                    </span>
                </button>
            </li>;
        })}
    </ol>
</nav>;

export default Stepper;
