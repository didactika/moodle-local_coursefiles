/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The migrations of FileBank: the list of them, the window that starts one and the page of each one, all in one application that
 * never leaves the page. The server works everything out; this only asks and shows.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_filebank/Migration
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useMemo, useState} from 'react';
import MigrationList from './migration/components/list/MigrationList';
import Review from './migration/components/review/Review';
import NewMigrationModal from './migration/components/wizard/NewMigrationModal';
import {useRoute} from './migration/hooks/useRoute';
import {describeError} from './migration/lib/errors';
import {createText} from './migration/lib/text';
import {getOptions} from './migration/services/api';
import type {MigrationProps, Options, Parameters} from './migration/types';
import Alert from './migration/components/ui/Alert';

/**
 * The migrations.
 *
 * @param props What the page gives the application: the migration to open, the sizes of a page and the text.
 * @returns The application.
 */
export default function Migration(props: MigrationProps) {
    const text = useMemo(() => createText(props.strings), [props.strings]);
    const route = useRoute(props.planid, props.pageurl);
    const [options, setOptions] = useState<Options | null>(null);
    const [error, setError] = useState('');
    const [creating, setCreating] = useState(false);
    const [initial, setInitial] = useState<Parameters | null>(null);

    useEffect(() => {
        // The grey blocks that held the place of the application are not needed any more.
        document.querySelectorAll('.fbm-boot').forEach((block) => block.remove());
        getOptions().then(setOptions).catch((problem: unknown) => setError(describeError(problem)));
    }, []);

    const openWizard = (parameters: Parameters | null) => {
        setInitial(parameters);
        setCreating(true);
    };

    return <div className="filebank-migration" data-region="fbm-app">
        {error !== '' && <Alert tone="danger" live>{error}</Alert>}
        {route.planid > 0
            ? <Review
                key={route.planid}
                id={route.planid}
                text={text}
                options={options}
                onBack={() => route.open(0)}
                onNew={openWizard}
            />
            : <MigrationList
                text={text}
                pagesizes={props.pagesizes}
                onOpen={route.open}
                onNew={() => openWizard(null)}
            />}
        <NewMigrationModal
            open={creating}
            options={options}
            initial={initial}
            text={text}
            onClose={() => setCreating(false)}
            onCreated={(id) => {
                setCreating(false);
                route.open(id);
            }}
        />
    </div>;
}
