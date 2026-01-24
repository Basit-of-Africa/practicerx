import PatientList from '../components/PatientList';

const Patients = () => {
    return (
        <div className="practicerx-page">
            <h1>Patients</h1>
            <p>View and manage patient records.</p>
            <PatientList />
        </div>
    );
};

export default Patients;
