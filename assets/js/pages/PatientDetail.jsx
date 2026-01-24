import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { useParams } from 'react-router-dom';
import EncounterForm from '../components/EncounterForm';

const PatientDetail = () => {
    const { id } = useParams();
    const [patient, setPatient] = useState(null);
    const [encounters, setEncounters] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const [patientData, encountersData] = await Promise.all([
                    apiFetch({ path: `/ppms/v1/patients/${id}` }),
                    apiFetch({ path: `/ppms/v1/patients/${id}/encounters` }),
                ]);
                setPatient(patientData);
                setEncounters(encountersData);
            } catch (error) {
                console.error(error);
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, [id]);

    const handleEncounterSaved = (newEncounter) => {
        setEncounters([newEncounter, ...encounters]);
    };

    if (loading) {
        return <div>Loading patient details...</div>;
    }

    if (!patient) {
        return <div>Patient not found.</div>;
    }

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

                    <div className="encounters-list">
                        {encounters.map((encounter) => (
                            <div key={encounter.id} className="encounter-card">
                                <div className="encounter-meta">
                                    <span className="type">{encounter.type}</span>
                                    <span className="date">{new Date(encounter.created_at).toLocaleDateString()}</span>
                                </div>
                                <div className="encounter-body">
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
