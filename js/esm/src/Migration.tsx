/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The migrations of FileBank: the list of them, the page that starts one or changes its parameters, and the page of each one, all
 * in one application that never leaves the page. The server works everything out; this only asks and shows.
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
import Alert from './migration/components/ui/Alert';
import MigrationWizard from './migration/components/wizard/MigrationWizard';
import {useRoute} from './migration/hooks/useRoute';
import {describeError} from './migration/lib/errors';
import {createText} from './migration/lib/text';
import {getOptions} from './migration/services/api';
import type {MigrationProps, Options} from './migration/types';

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

    useEffect(() => {
        // The grey blocks that held the place of the application are not needed any more.
        document.querySelectorAll('.fbm-boot').forEach((block) => block.remove());
        getOptions().then(setOptions).catch((problem: unknown) => setError(describeError(problem)));
    }, []);

    let screen;
    if (route.mode === 'new' || route.mode === 'edit') {
        screen = <MigrationWizard
            key={`${route.mode}-${route.planid}-${route.from}`}
            mode={route.mode}
            options={options}
            migrationId={route.mode === 'edit' ? route.planid : route.from}
            text={text}
            onClose={() => (route.mode === 'edit' ? route.openReview(route.planid) : route.openList())}
            onDone={route.openReview}
        />;
    } else if (route.mode === 'review') {
        screen = <Review
            key={route.planid}
            id={route.planid}
            text={text}
            onBack={route.openList}
            onEdit={() => route.openEdit(route.planid)}
            onNew={() => route.openNew(route.planid)}
        />;
    } else {
        screen = <MigrationList text={text} pagesizes={props.pagesizes} onOpen={route.openReview} onNew={() => route.openNew()} />;
    }

    return <div className="filebank-migration" data-region="fbm-app">
        {error !== '' && <Alert tone="danger" live>{error}</Alert>}
        {screen}
    </div>;
}
