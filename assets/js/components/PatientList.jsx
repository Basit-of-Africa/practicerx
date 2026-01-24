import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { Link } from 'react-router-dom';

const PatientList = () => {
    const [patients, setPatients] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        apiFetch({ path: '/ppms/v1/patients' })
            .then((data) => {
                setPatients(data);
                setLoading(false);
            })
            .catch((error) => console.error(error));
    }, []);

    if (loading) {
        return <div>Loading patients...</div>;
    }

    return (
        <div className="practicerx-patient-list">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {patients.map((patient) => (
                        <tr key={patient.id}>
                            <td>{patient.id}</td>
                            <td>{patient.phone}</td>
                            <td>{patient.gender}</td>
                            <td>
                                <Link to={`/patients/${patient.id}`}>View</Link>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default PatientList;
