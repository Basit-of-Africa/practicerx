import ClientLayout from '../components/ClientLayout';
import AppointmentCalendar from '../components/AppointmentCalendar';

const ClientAppointments = () => {
    const token = typeof window !== 'undefined' ? localStorage.getItem('ppms_token') : null;

    return (
        <ClientLayout>
            <div style={{ padding: 20 }}>
                <h1>Appointments Calendar</h1>
                <AppointmentCalendar token={token} />
            </div>
        </ClientLayout>
    );
};

export default ClientAppointments;
