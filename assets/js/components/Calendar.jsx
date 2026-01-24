import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const Calendar = () => {
    const [appointments, setAppointments] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchAppointments = async () => {
            try {
                const start = new Date().toISOString().split('T')[0];
                const end = new Date(new Date().setDate(new Date().getDate() + 30)).toISOString().split('T')[0];

                const response = await apiFetch({
                    path: `/ppms/v1/appointments?start_date=${start}&end_date=${end}`,
                });
                setAppointments(response);
            } catch (error) {
                console.error(error);
            } finally {
                setLoading(false);
            }
        };

        fetchAppointments();
    }, []);

    if (loading) {
        return <div>Loading calendar...</div>;
    }

    return (
        <div className="practicerx-calendar">
            <h3>Upcoming Appointments</h3>
            {appointments.length === 0 ? (
                <p>No appointments found.</p>
            ) : (
                <ul>
                    {appointments.map((appt) => (
                        <li key={appt.id}>
                            {appt.start_time} - {appt.status}
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
};

export default Calendar;
