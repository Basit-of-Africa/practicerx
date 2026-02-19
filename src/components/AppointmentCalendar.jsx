import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';

const AppointmentCalendar = ({ token }) => {
    const fetchEvents = (info, successCallback, failureCallback) => {
        const start = info.startStr.replace('T', ' ');
        const end = info.endStr.replace('T', ' ');
        const headers = { 'Content-Type': 'application/json' };
        if (token) headers['Authorization'] = 'Bearer ' + token;

        fetch(practicerxSettings.root + `appointments/client?start_date=${encodeURIComponent(start)}&end_date=${encodeURIComponent(end)}`, { headers })
            .then(async (res) => {
                if (!res.ok) {
                    failureCallback('Failed to load events');
                    return;
                }
                const data = await res.json();
                const events = (Array.isArray(data) ? data : []).map((a) => ({
                    id: a.id,
                    title: a.title || 'Appointment',
                    start: a.start_time,
                    end: a.end_time,
                    extendedProps: { status: a.status }
                }));
                successCallback(events);
            }).catch((err) => failureCallback(err));
    };

    return (
        <FullCalendar
            plugins={[ dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin ]}
            initialView="dayGridMonth"
            headerToolbar={{ left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' }}
            events={fetchEvents}
            height="auto"
        />
    );
};

export default AppointmentCalendar;
