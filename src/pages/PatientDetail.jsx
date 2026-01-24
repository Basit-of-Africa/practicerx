import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import EncounterForm from '../components/EncounterForm';

const PatientDetail = ({ id }) => {
    const [patient, setPatient] = useState(null);
    const [encounters, setEncounters] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        Promise.all([
            apiFetch({ path: `/ppms/v1/patients/${id}` }),
            apiFetch({ path: `/ppms/v1/patients/${id}/encounters` })
        ]).then((results) => {
            setPatient(results[0]);
            setEncounters(results[1]);
        }).catch((error) => {
            console.error(error);
        }).finally(() => {
            setLoading(false);
        });
    }, [id]);

    const handleEncounterSaved = (newEncounter) => {
        setEncounters([newEncounter, ...encounters]);
    };

    if (loading) return <div>Loading patient details...</div>;
    if (!patient) return <div>Patient not found.</div>;

    return (
        <div className="practicerx-page practicerx-patient-detail">
            <div className="patient-header">
                <h1>Patient #{patient.id}</h1>
                <div className="patient-info">
                    <p><strong>Phone:</strong> {patient.phone}</p>
                    <p><strong>Gender:</strong> {patient.gender}</p>
                    <p><strong>DOB:</strong> {patient.dob}</p>
                </div>
            </div>

            <div className="patient-content">
                <div className="encounters-section">
                    <h3>Clinical History</h3>
                    <EncounterForm patientId={patient.id} onSave={handleEncounterSaved} />

                    <div className="encounters-list" style={{ marginTop: '20px' }}>
                        {encounters.map((encounter) => (
                            <div key={encounter.id} className="encounter-card" style={{ background: '#fff', border: '1px solid #ddd', padding: '15px', marginBottom: '10px' }}>
                                <div className="encounter-meta" style={{ borderBottom: '1px solid #eee', paddingBottom: '5px', marginBottom: '5px', fontSize: '0.9em', color: '#666' }}>
                                    <span className="type" style={{ textTransform: 'uppercase', fontWeight: 'bold', marginRight: '10px' }}>{encounter.type}</span>
                                    <span className="date">{new Date(encounter.created_at).toLocaleDateString()}</span>
                                </div>
                                <div className="encounter-body" style={{ whiteSpace: 'pre-wrap' }}>
                                    {encounter.content}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default PatientDetail;
