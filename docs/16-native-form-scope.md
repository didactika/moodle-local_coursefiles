# Owning-form binding for draft batches

An explicitly integrated form can register `form/scoped_filemanager` as a custom QuickForm element. It inherits Moodle's native filemanager presentation, validation and saving behavior. Its server-side contract is read from the live element; browser requests cannot supply destination limits.

Inherited native QuickForm filemanager/editor elements bind authorized activity fields on the supported controllers, preserving rendering and saving. Other controls delegate unchanged. The real field is required; request-supplied draft IDs or limits cannot enable batches. The [Folder Settings integration](17-folder-selection.md) and [File Settings integration](18-resource-selection.md) bind standard native filemanagers through this API; the repository embeds the session-bound visual browser inside the native dialog.

## Register and bind a native control

After loading Moodle's Forms API, the owning form registers the element and adds it using native options:

```php
\local_multipicker\form\scoped_filemanager::register();
$mform->addElement('multipickerfilemanager', 'files', get_string('files'), null, [
    'subdirs' => true,
    'maxfiles' => 20,
    'maxbytes' => 10485760,
    'areamaxbytes' => 52428800,
    'accepted_types' => ['document'],
    'return_types' => FILE_INTERNAL,
]);
```

After preparing defaults or submitted data, obtain the element and call `open_scope($context)` before its first display. Supply the owning form's actual course/module context. An empty new control obtains its own native draft; a positive draft value must already have the destination directory in the current account's draft area.

The element reads its field name, draft ID, count limit, area limit, directory setting, accepted types and return-type eligibility. It normalizes accepted types with Moodle's native utility and resolves the per-file ceiling with the same context/site/course/control helper used by the native renderer. Frozen fields and controls that disallow internal copies cannot create a writable binding.

## Session and approval lifecycle

`draft_scope` stores the actor, course, context, field, draft, directory and native limits in Moodle's session. The returned random 256-bit token identifies this binding. It does not replace the current session key or authorization checks.

![Owning form scope sequence](diagrams/design/sequences/form-scope.svg)

| Operation | Inputs | Behavior |
|---|---|---|
| `open_scope` | Live element and server-owned context | Creates a binding with a 30-minute lifetime |
| `draft_scope::destinations` | Token and session key | Lists existing directories in the bound actor-owned draft, respecting the native subdirectory setting |
| `draft_scope::select_destination` | Token, existing directory path, session key | Rechecks draft ownership and directory existence; changes clear the stored approval |
| `draft_scope::review` | Token, profile, selectors, session key | Uses the stored destination/limits and remembers the reviewed selection and digest |
| `draft_scope::apply` | Token, approved digest, session key | Applies exactly the remembered selection; no destination or limit override is accepted |
| `draft_scope::close` | Token and session key | Releases the binding and retains native draft contents |

Each request checks the session key, current actor, expiry and current course/module editing capability. Context identity and current upload ceilings are checked again. A smaller ceiling can invalidate an earlier approval. Rebinding a field/draft/context invalidates its previous token. At most 32 bindings are retained per session, with expired entries pruned during registration.

Owning forms must validate the visible destination through `select_destination` before reviewing or applying. Changing the path clears the previous approval, so an apply request cannot reuse an old digest for another directory. Only existing directories in the bound draft are accepted; callers cannot select another draft's directory or bypass `subdirs`. The approved folder mode maps source paths below that directory, and combined paths must fit Moodle's 255-character filepath column. Creation and permanent saving remain the native form's responsibility.

An apply attempt consumes its stored approval, including failed attempts; a new review is required for another attempt. Successful copying remains inside the current native draft. The owning controller must handle ordinary authentication, normal form saving/cancellation and explicit scope release. Logout loses session-local bindings; an abandoned form's binding expires rather than detecting a browser tab close.

## Native form submission and rendering

The test fixture uses a real registered QuickForm element and a native no-submit refresh button. A simulated form submission retains the pending title and the same draft ID. `get_data()` returns no save payload for that refresh, and the native control renders again after the batch is added. The destination activity remains unchanged until the normal File API save.

This establishes the developer element's PHP round trip, including native registration and rendering. The separately documented Folder and File Settings consumers provide the shipped native selection screens.

Thirteen integration cases also cover session-key rejection, stolen actor bindings, expiry, changed native limits, changed upload ceilings, explicit close, replay rejection, frozen controls, new and foreign drafts, current permission revocation and bounded session storage. See [validation](07-validation-and-decisions.md) and the underlying [batch contract](15-draft-batches.md).

The implementation uses the [custom form element API](https://moodledev.io/docs/5.3/apis/subsystems/form#custom-form-elements) and retains the native [draft/save lifecycle](https://moodledev.io/docs/4.5/apis/subsystems/form/usage/files).


## Embedded native consumer

`form/native_filemanager` registers through `after_config` and binds the populated field during native rendering. A newly allocated native draft receives its current-user root directory; an existing positive draft is validated. `/selection/index.php` uses an embedded Moodle layout and server-rendered Mustache cards. `browser_listing` supplies authorized, paged contents.

`select_mode`, `remember_view` and `approved` invalidate or revalidate the stored review. Scope resolution checks current Folder content permission or activity-management permission as appropriate. The companion repository receives a session-approved batch descriptor, validates native context/draft identity, and calls `apply` without accepting browser-provided limits. Its response refreshes the original filemanager.
