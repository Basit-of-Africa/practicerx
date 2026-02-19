import { render } from '@wordpress/element';
import { Router, RouterContext } from './utils/Router';
import Layout from './components/Layout';
import Dashboard from './pages/Dashboard';
import Appointments from './pages/Appointments';
import Patients from './pages/Patients';
import PatientDetail from './pages/PatientDetail';
import Settings from './pages/Settings';
import Telehealth from './pages/Telehealth';
import Campaigns from './pages/Campaigns';
import Recipes from './pages/Recipes';
import MealPlans from './pages/MealPlans';
import Forms from './pages/Forms';
import Documents from './pages/Documents';
import HealthTracking from './pages/HealthTracking';
import ClientDashboard from './pages/ClientDashboard';
import ClientLogin from './pages/ClientLogin';
import ClientRegister from './pages/ClientRegister';

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
                    else if (path === '/telehealth') content = <Telehealth />;
                    else if (path === '/campaigns') content = <Campaigns />;
                    else if (path === '/recipes') content = <Recipes />;
                    else if (path === '/meal-plans') content = <MealPlans />;
                    else if (path === '/forms') content = <Forms />;
                    else if (path === '/documents') content = <Documents />;
                    else if (path === '/health-tracking') content = <HealthTracking />;
                    else if (path === '/settings') content = <Settings />;
                    else if (path === '/client' || path === '/client/') content = <ClientDashboard />;
                    else if (path === '/client/login') content = <ClientLogin />;
                    else if (path === '/client/register') content = <ClientRegister />;
                    else if (path.startsWith('/client/')) content = <ClientDashboard />;
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
