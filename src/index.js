import { render } from '@wordpress/element';
import { Router, RouterContext } from './utils/Router';
import Layout from './components/Layout';
import Dashboard from './pages/Dashboard';
import Appointments from './pages/Appointments';
import Patients from './pages/Patients';
import PatientDetail from './pages/PatientDetail';
import Settings from './pages/Settings';

const App = () => {
    return (
        <Router>
            <RouterContext.Consumer>
                {({ path }) => {
                    let content;
                    if (path === '/') content = <Dashboard />;
                    else if (path === '/appointments') content = <Appointments />;
                    else if (path === '/patients') content = <Patients />;
                    else if (path.startsWith('/patients/')) content = <PatientDetail id={path.split('/')[2]} />;
                    else if (path === '/settings') content = <Settings />;
                    else content = <div>Page not found</div>;

                    return <Layout>{content}</Layout>;
                }}
            </RouterContext.Consumer>
        </Router>
    );
};

const root = document.getElementById('practicerx-root');
if (root) {
    render(<App />, root);
}
