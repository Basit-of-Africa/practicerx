import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const MealPlans = () => {
    const [plans, setPlans] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadPlans();
    }, []);

    const loadPlans = async () => {
        try {
            const data = await apiFetch({ path: '/ppms/v1/meal-plans' });
            setPlans(data.data || []);
        } catch (error) {
            console.error('Error loading meal plans:', error);
        } finally {
            setLoading(false);
        }
    };

    if (loading) return <div>Loading meal plans...</div>;

    return (
        <div>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '20px' }}>
                <h1>Meal Plans</h1>
                <button
                    onClick={() => alert('Meal plan builder coming soon!')}
                    style={{
                        padding: '10px 20px',
                        background: '#0073aa',
                        color: '#fff',
                        border: 'none',
                        borderRadius: '4px',
                        cursor: 'pointer'
                    }}
                >
                    Create Meal Plan
                </button>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(350px, 1fr))', gap: '20px' }}>
                {plans.length === 0 ? (
                    <div style={{ padding: '40px', textAlign: 'center', color: '#666' }}>
                        No meal plans created
                    </div>
                ) : (
                    plans.map(plan => (
                        <div key={plan.id} style={{
                            background: '#fff',
                            border: '1px solid #ddd',
                            borderRadius: '4px',
                            padding: '20px'
                        }}>
                            <h3 style={{ margin: '0 0 10px 0' }}>{plan.name}</h3>
                            <p style={{ color: '#666', fontSize: '13px', marginBottom: '15px' }}>
                                {plan.description || 'No description'}
                            </p>
                            <div style={{ fontSize: '12px', color: '#666', marginBottom: '15px' }}>
                                <div>Duration: {plan.duration_days} days</div>
                                <div>Target: {plan.calories_target || 0} calories/day</div>
                                {plan.is_template === 1 && (
                                    <div style={{
                                        marginTop: '5px',
                                        padding: '4px 8px',
                                        background: '#0073aa',
                                        color: '#fff',
                                        borderRadius: '4px',
                                        display: 'inline-block'
                                    }}>
                                        Template
                                    </div>
                                )}
                            </div>
                            <button
                                onClick={() => alert('View plan details coming soon!')}
                                style={{
                                    padding: '6px 12px',
                                    background: '#0073aa',
                                    color: '#fff',
                                    border: 'none',
                                    borderRadius: '4px',
                                    cursor: 'pointer',
                                    fontSize: '12px'
                                }}
                            >
                                View Plan
                            </button>
                        </div>
                    ))
                )}
            </div>
        </div>
    );
};

export default MealPlans;
