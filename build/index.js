(function (wp) {
    var el = wp.element.createElement;
    var useState = wp.element.useState;
    var useEffect = wp.element.useEffect;
    var render = wp.element.render;
    var apiFetch = wp.apiFetch;

    // --- Router Context ---
    var RouterContext = wp.element.createContext({
        path: '/',
        navigate: function () { }
    });

    function Router(props) {
        var [path, setPath] = useState(window.location.hash.substr(1) || '/');

        useEffect(function () {
            function handleHashChange() {
                setPath(window.location.hash.substr(1) || '/');
            }
            window.addEventListener('hashchange', handleHashChange);
            return function () {
                window.removeEventListener('hashchange', handleHashChange);
            };
        }, []);

        var navigate = function (newPath) {
            window.location.hash = newPath;
        };

        return el(RouterContext.Provider, { value: { path: path, navigate: navigate } }, props.children);
    }

    function Link(props) {
        return el('a', {
            href: '#' + props.to,
            className: props.className,
            style: { cursor: 'pointer', color: '#0073aa', textDecoration: 'none' }
        }, props.children);
    }

    // --- Components ---

    function Layout(props) {
        return el('div', { className: 'practicerx-layout', style: { display: 'flex', minHeight: '100vh' } },
            el('div', { className: 'practicerx-sidebar', style: { width: '200px', background: '#fff', borderRight: '1px solid #ddd', padding: '20px' } },
                el('h2', null, 'PracticeRx'),
                el('nav', null,
                    el('ul', { style: { listStyle: 'none', padding: 0 } },
                        el('li', { style: { marginBottom: '10px' } }, el(Link, { to: '/' }, 'Dashboard')),
                        el('li', { style: { marginBottom: '10px' } }, el(Link, { to: '/appointments' }, 'Appointments')),
                        el('li', { style: { marginBottom: '10px' } }, el(Link, { to: '/patients' }, 'Patients')),
                        el('li', { style: { marginBottom: '10px' } }, el(Link, { to: '/settings' }, 'Settings'))
                    )
                )
            ),
            el('div', { className: 'practicerx-content', style: { flex: 1, padding: '20px' } }, props.children)
        );
    }

    function Calendar() {
        var [appointments, setAppointments] = useState([]);
        var [loading, setLoading] = useState(true);

        useEffect(function () {
            var start = new Date().toISOString().split('T')[0];
            var end = new Date(new Date().setDate(new Date().getDate() + 30)).toISOString().split('T')[0];

            apiFetch({ path: '/ppms/v1/appointments?start_date=' + start + '&end_date=' + end })
                .then(function (data) { setAppointments(data); })
                .catch(function (error) { console.error(error); })
                .finally(function () { setLoading(false); });
        }, []);

        if (loading) return el('div', null, 'Loading calendar...');

        return el('div', { className: 'practicerx-calendar' },
            el('h3', null, 'Upcoming Appointments'),
            appointments.length === 0
                ? el('p', null, 'No appointments found.')
                : el('ul', null, appointments.map(function (appt) {
                    return el('li', { key: appt.id }, appt.start_time + ' - ' + appt.status);
                }))
        );
    }

    function PatientList() {
        var [patients, setPatients] = useState([]);
        var [loading, setLoading] = useState(true);

        useEffect(function () {
            apiFetch({ path: '/ppms/v1/patients' })
                .then(function (data) { setPatients(data); })
                .catch(function (error) { console.error(error); })
                .finally(function () { setLoading(false); });
        }, []);

        if (loading) return el('div', null, 'Loading patients...');

        return el('div', { className: 'practicerx-patient-list' },
            el('table', { className: 'wp-list-table widefat fixed striped' },
                el('thead', null,
                    el('tr', null,
                        el('th', null, 'ID'),
                        el('th', null, 'Phone'),
                        el('th', null, 'Gender'),
                        el('th', null, 'Actions')
                    )
                ),
                el('tbody', null, patients.map(function (patient) {
                    return el('tr', { key: patient.id },
                        el('td', null, patient.id),
                        el('td', null, patient.phone),
                        el('td', null, patient.gender),
                        el('td', null, el(Link, { to: '/patients/' + patient.id }, 'View'))
                    );
                }))
            )
        );
    }

    function EncounterForm(props) {
        var [content, setContent] = useState('');
        var [type, setType] = useState('general');
        var [isSaving, setIsSaving] = useState(false);

        var handleSubmit = function (e) {
            e.preventDefault();
            setIsSaving(true);

            apiFetch({
                path: '/ppms/v1/encounters',
                method: 'POST',
                data: {
                    patient_id: props.patientId,
                    practitioner_id: 1,
                    type: type,
                    content: content,
                },
            }).then(function (response) {
                setContent('');
                if (props.onSave) props.onSave(response);
            }).catch(function (error) {
                console.error(error);
                alert('Failed to save encounter.');
            }).finally(function () {
                setIsSaving(false);
            });
        };

        return el('div', { className: 'practicerx-encounter-form', style: { marginTop: '20px', padding: '15px', background: '#f9f9f9', border: '1px solid #ddd' } },
            el('h4', null, 'Add Clinical Note'),
            el('form', { onSubmit: handleSubmit },
                el('div', { className: 'form-group', style: { marginBottom: '10px' } },
                    el('label', { style: { display: 'block' } }, 'Type'),
                    el('select', { value: type, onChange: function (e) { setType(e.target.value); }, style: { width: '100%' } },
                        el('option', { value: 'general' }, 'General Note'),
                        el('option', { value: 'soap' }, 'SOAP Note'),
                        el('option', { value: 'assessment' }, 'Assessment'),
                        el('option', { value: 'prescription' }, 'Prescription')
                    )
                ),
                el('div', { className: 'form-group', style: { marginBottom: '10px' } },
                    el('label', { style: { display: 'block' } }, 'Content'),
                    el('textarea', { value: content, onChange: function (e) { setContent(e.target.value); }, rows: 5, required: true, style: { width: '100%' } })
                ),
                el('button', { type: 'submit', disabled: isSaving, className: 'button button-primary' }, isSaving ? 'Saving...' : 'Save Note')
            )
        );
    }

    function PatientDetail(props) {
        var [patient, setPatient] = useState(null);
        var [encounters, setEncounters] = useState([]);
        var [loading, setLoading] = useState(true);
        var id = props.id;

        useEffect(function () {
            Promise.all([
                apiFetch({ path: '/ppms/v1/patients/' + id }),
                apiFetch({ path: '/ppms/v1/patients/' + id + '/encounters' })
            ]).then(function (results) {
                setPatient(results[0]);
                setEncounters(results[1]);
            }).catch(function (error) {
                console.error(error);
            }).finally(function () {
                setLoading(false);
            });
        }, [id]);

        var handleEncounterSaved = function (newEncounter) {
            setEncounters([newEncounter].concat(encounters));
        };

        if (loading) return el('div', null, 'Loading patient details...');
        if (!patient) return el('div', null, 'Patient not found.');

        return el('div', { className: 'practicerx-page practicerx-patient-detail' },
            el('div', { className: 'patient-header' },
                el('h1', null, 'Patient #' + patient.id),
                el('div', { className: 'patient-info' },
                    el('p', null, el('strong', null, 'Phone: '), patient.phone),
                    el('p', null, el('strong', null, 'Gender: '), patient.gender),
                    el('p', null, el('strong', null, 'DOB: '), patient.dob)
                )
            ),
            el('div', { className: 'patient-content' },
                el('div', { className: 'encounters-section' },
                    el('h3', null, 'Clinical History'),
                    el(EncounterForm, { patientId: patient.id, onSave: handleEncounterSaved }),
                    el('div', { className: 'encounters-list', style: { marginTop: '20px' } },
                        encounters.map(function (encounter) {
                            return el('div', { key: encounter.id, className: 'encounter-card', style: { background: '#fff', border: '1px solid #ddd', padding: '15px', marginBottom: '10px' } },
                                el('div', { className: 'encounter-meta', style: { borderBottom: '1px solid #eee', paddingBottom: '5px', marginBottom: '5px', fontSize: '0.9em', color: '#666' } },
                                    el('span', { className: 'type', style: { textTransform: 'uppercase', fontWeight: 'bold', marginRight: '10px' } }, encounter.type),
                                    el('span', { className: 'date' }, new Date(encounter.created_at).toLocaleDateString())
                                ),
                                el('div', { className: 'encounter-body', style: { whiteSpace: 'pre-wrap' } }, encounter.content)
                            );
                        })
                    )
                )
            )
        );
    }

    function Dashboard() {
        var [showDemoPrompt, setShowDemoPrompt] = useState(false);
        var [importing, setImporting] = useState(false);

        useEffect(function () {
            apiFetch({ path: '/ppms/v1/patients' }).then(function (data) {
                if (data.length === 0) setShowDemoPrompt(true);
            });
        }, []);

        var handleImport = function () {
            setImporting(true);
            apiFetch({ path: '/ppms/v1/system/seed', method: 'POST' })
                .then(function () {
                    setShowDemoPrompt(false);
                    alert('Demo data imported successfully! Refresh the page to see it.');
                })
                .catch(function (error) {
                    console.error(error);
                    alert('Import failed.');
                })
                .finally(function () { setImporting(false); });
        };

        return el('div', { className: 'practicerx-page' },
            el('h1', null, 'Dashboard'),
            el('p', null, 'Welcome to PracticeRx.'),
            showDemoPrompt && el('div', { className: 'notice notice-info inline', style: { padding: '15px', margin: '20px 0' } },
                el('h3', null, 'Get Started Quickly'),
                el('p', null, 'It looks like this is a fresh installation. Would you like to import some demo data?'),
                el('button', { className: 'button button-primary', onClick: handleImport, disabled: importing },
                    importing ? 'Importing...' : 'Import Demo Data'
                )
            )
        );
    }

    function Settings() {
        return el('div', { className: 'practicerx-page' },
            el('h1', null, 'Settings'),
            el('p', null, 'Configure your practice settings.')
        );
    }

    function Appointments() {
        return el('div', { className: 'practicerx-page' },
            el('h1', null, 'Appointments'),
            el('p', null, 'Manage your schedule here.'),
            el(Calendar)
        );
    }

    function Patients() {
        return el('div', { className: 'practicerx-page' },
            el('h1', null, 'Patients'),
            el('p', null, 'View and manage patient records.'),
            el(PatientList)
        );
    }

    // --- Main App ---

    function App() {
        return el(Router, null,
            el(RouterContext.Consumer, null, function (context) {
                var path = context.path;
                var content;

                if (path === '/') content = el(Dashboard);
                else if (path === '/appointments') content = el(Appointments);
                else if (path === '/patients') content = el(Patients);
                else if (path.startsWith('/patients/')) content = el(PatientDetail, { id: path.split('/')[2] });
                else if (path === '/settings') content = el(Settings);
                else content = el('div', null, 'Page not found');

                return el(Layout, null, content);
            })
        );
    }

    var root = document.getElementById('practicerx-root');
    if (root) {
        render(el(App), root);
    }

})(window.wp);
