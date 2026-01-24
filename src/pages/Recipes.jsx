import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const Recipes = () => {
    const [recipes, setRecipes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [formData, setFormData] = useState({
        title: '',
        description: '',
        meal_type: 'lunch',
        prep_time: 0,
        cook_time: 0,
        servings: 1,
        calories: 0,
        protein: 0,
        carbs: 0,
        fats: 0,
        ingredients: [''],
        instructions: [''],
        tags: '',
        is_public: false
    });

    useEffect(() => {
        loadRecipes();
    }, []);

    const loadRecipes = async () => {
        try {
            const data = await apiFetch({ path: '/ppms/v1/recipes' });
            setRecipes(data.data || []);
        } catch (error) {
            console.error('Error loading recipes:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const payload = {
                ...formData,
                practitioner_id: window.practicerxSettings?.currentUserId || 1
            };
            if (editingId) {
                await apiFetch({
                    path: `/ppms/v1/recipes/${editingId}`,
                    method: 'PUT',
                    data: payload
                });
            } else {
                await apiFetch({
                    path: '/ppms/v1/recipes',
                    method: 'POST',
                    data: payload
                });
            }
            resetForm();
            loadRecipes();
        } catch (error) {
            alert('Error saving recipe: ' + error.message);
        }
    };

    const handleEdit = (recipe) => {
        setFormData({
            title: recipe.title,
            description: recipe.description || '',
            meal_type: recipe.meal_type,
            prep_time: recipe.prep_time,
            cook_time: recipe.cook_time,
            servings: recipe.servings,
            calories: recipe.calories,
            protein: recipe.protein,
            carbs: recipe.carbs,
            fats: recipe.fats,
            ingredients: recipe.ingredients || [''],
            instructions: recipe.instructions || [''],
            tags: recipe.tags || '',
            is_public: recipe.is_public === 1
        });
        setEditingId(recipe.id);
        setShowForm(true);
    };

    const handleDelete = async (id) => {
        if (!confirm('Delete this recipe?')) return;
        try {
            await apiFetch({
                path: `/ppms/v1/recipes/${id}`,
                method: 'DELETE'
            });
            loadRecipes();
        } catch (error) {
            alert('Error deleting recipe: ' + error.message);
        }
    };

    const resetForm = () => {
        setFormData({
            title: '',
            description: '',
            meal_type: 'lunch',
            prep_time: 0,
            cook_time: 0,
            servings: 1,
            calories: 0,
            protein: 0,
            carbs: 0,
            fats: 0,
            ingredients: [''],
            instructions: [''],
            tags: '',
            is_public: false
        });
        setEditingId(null);
        setShowForm(false);
    };

    const addIngredient = () => {
        setFormData({ ...formData, ingredients: [...formData.ingredients, ''] });
    };

    const removeIngredient = (index) => {
        const newIngredients = formData.ingredients.filter((_, i) => i !== index);
        setFormData({ ...formData, ingredients: newIngredients });
    };

    const updateIngredient = (index, value) => {
        const newIngredients = [...formData.ingredients];
        newIngredients[index] = value;
        setFormData({ ...formData, ingredients: newIngredients });
    };

    const addInstruction = () => {
        setFormData({ ...formData, instructions: [...formData.instructions, ''] });
    };

    const removeInstruction = (index) => {
        const newInstructions = formData.instructions.filter((_, i) => i !== index);
        setFormData({ ...formData, instructions: newInstructions });
    };

    const updateInstruction = (index, value) => {
        const newInstructions = [...formData.instructions];
        newInstructions[index] = value;
        setFormData({ ...formData, instructions: newInstructions });
    };

    if (loading) return <div>Loading recipes...</div>;

    return (
        <div>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '20px' }}>
                <h1>Recipe Library</h1>
                <button
                    onClick={() => setShowForm(!showForm)}
                    style={{
                        padding: '10px 20px',
                        background: '#0073aa',
                        color: '#fff',
                        border: 'none',
                        borderRadius: '4px',
                        cursor: 'pointer'
                    }}
                >
                    {showForm ? 'Cancel' : 'Add Recipe'}
                </button>
            </div>

            {showForm && (
                <div style={{
                    background: '#fff',
                    padding: '20px',
                    marginBottom: '20px',
                    border: '1px solid #ddd',
                    borderRadius: '4px'
                }}>
                    <h2>{editingId ? 'Edit Recipe' : 'Add Recipe'}</h2>
                    <form onSubmit={handleSubmit}>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '15px' }}>
                            <div>
                                <label style={{ display: 'block', marginBottom: '5px' }}>Recipe Title</label>
                                <input
                                    type="text"
                                    value={formData.title}
                                    onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                                    required
                                    style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                                />
                            </div>
                            <div>
                                <label style={{ display: 'block', marginBottom: '5px' }}>Meal Type</label>
                                <select
                                    value={formData.meal_type}
                                    onChange={(e) => setFormData({ ...formData, meal_type: e.target.value })}
                                    style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                                >
                                    <option value="breakfast">Breakfast</option>
                                    <option value="lunch">Lunch</option>
                                    <option value="dinner">Dinner</option>
                                    <option value="snack">Snack</option>
                                </select>
                            </div>
                        </div>

                        <div style={{ marginTop: '15px' }}>
                            <label style={{ display: 'block', marginBottom: '5px' }}>Description</label>
                            <textarea
                                value={formData.description}
                                onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                                rows="2"
                                style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                            />
                        </div>

                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '15px', marginTop: '15px' }}>
                            <div>
                                <label style={{ display: 'block', marginBottom: '5px' }}>Prep Time (min)</label>
                                <input
                                    type="number"
                                    value={formData.prep_time}
                                    onChange={(e) => setFormData({ ...formData, prep_time: parseInt(e.target.value) })}
                                    style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                                />
                            </div>
                            <div>
                                <label style={{ display: 'block', marginBottom: '5px' }}>Cook Time (min)</label>
                                <input
                                    type="number"
                                    value={formData.cook_time}
                                    onChange={(e) => setFormData({ ...formData, cook_time: parseInt(e.target.value) })}
                                    style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                                />
                            </div>
                            <div>
                                <label style={{ display: 'block', marginBottom: '5px' }}>Servings</label>
                                <input
                                    type="number"
                                    value={formData.servings}
                                    onChange={(e) => setFormData({ ...formData, servings: parseInt(e.target.value) })}
                                    style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                                />
                            </div>
                        </div>

                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr 1fr', gap: '15px', marginTop: '15px' }}>
                            <div>
                                <label style={{ display: 'block', marginBottom: '5px' }}>Calories</label>
                                <input
                                    type="number"
                                    value={formData.calories}
                                    onChange={(e) => setFormData({ ...formData, calories: parseInt(e.target.value) })}
                                    style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                                />
                            </div>
                            <div>
                                <label style={{ display: 'block', marginBottom: '5px' }}>Protein (g)</label>
                                <input
                                    type="number"
                                    step="0.1"
                                    value={formData.protein}
                                    onChange={(e) => setFormData({ ...formData, protein: parseFloat(e.target.value) })}
                                    style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                                />
                            </div>
                            <div>
                                <label style={{ display: 'block', marginBottom: '5px' }}>Carbs (g)</label>
                                <input
                                    type="number"
                                    step="0.1"
                                    value={formData.carbs}
                                    onChange={(e) => setFormData({ ...formData, carbs: parseFloat(e.target.value) })}
                                    style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                                />
                            </div>
                            <div>
                                <label style={{ display: 'block', marginBottom: '5px' }}>Fats (g)</label>
                                <input
                                    type="number"
                                    step="0.1"
                                    value={formData.fats}
                                    onChange={(e) => setFormData({ ...formData, fats: parseFloat(e.target.value) })}
                                    style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                                />
                            </div>
                        </div>

                        <div style={{ marginTop: '15px' }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' }}>
                                <label style={{ fontWeight: 'bold' }}>Ingredients</label>
                                <button
                                    type="button"
                                    onClick={addIngredient}
                                    style={{
                                        padding: '6px 12px',
                                        background: '#00a32a',
                                        color: '#fff',
                                        border: 'none',
                                        borderRadius: '4px',
                                        cursor: 'pointer',
                                        fontSize: '12px'
                                    }}
                                >
                                    + Add
                                </button>
                            </div>
                            {formData.ingredients.map((ingredient, index) => (
                                <div key={index} style={{ display: 'flex', gap: '10px', marginBottom: '8px' }}>
                                    <input
                                        type="text"
                                        value={ingredient}
                                        onChange={(e) => updateIngredient(index, e.target.value)}
                                        placeholder="e.g., 1 cup flour"
                                        style={{ flex: 1, padding: '6px', border: '1px solid #ddd', borderRadius: '4px' }}
                                    />
                                    {formData.ingredients.length > 1 && (
                                        <button
                                            type="button"
                                            onClick={() => removeIngredient(index)}
                                            style={{
                                                padding: '6px 12px',
                                                background: '#dc3232',
                                                color: '#fff',
                                                border: 'none',
                                                borderRadius: '4px',
                                                cursor: 'pointer'
                                            }}
                                        >
                                            ✕
                                        </button>
                                    )}
                                </div>
                            ))}
                        </div>

                        <div style={{ marginTop: '15px' }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' }}>
                                <label style={{ fontWeight: 'bold' }}>Instructions</label>
                                <button
                                    type="button"
                                    onClick={addInstruction}
                                    style={{
                                        padding: '6px 12px',
                                        background: '#00a32a',
                                        color: '#fff',
                                        border: 'none',
                                        borderRadius: '4px',
                                        cursor: 'pointer',
                                        fontSize: '12px'
                                    }}
                                >
                                    + Add
                                </button>
                            </div>
                            {formData.instructions.map((instruction, index) => (
                                <div key={index} style={{ display: 'flex', gap: '10px', marginBottom: '8px' }}>
                                    <span style={{ padding: '6px 10px', background: '#f0f0f0', borderRadius: '4px' }}>
                                        {index + 1}
                                    </span>
                                    <input
                                        type="text"
                                        value={instruction}
                                        onChange={(e) => updateInstruction(index, e.target.value)}
                                        placeholder="Step instructions"
                                        style={{ flex: 1, padding: '6px', border: '1px solid #ddd', borderRadius: '4px' }}
                                    />
                                    {formData.instructions.length > 1 && (
                                        <button
                                            type="button"
                                            onClick={() => removeInstruction(index)}
                                            style={{
                                                padding: '6px 12px',
                                                background: '#dc3232',
                                                color: '#fff',
                                                border: 'none',
                                                borderRadius: '4px',
                                                cursor: 'pointer'
                                            }}
                                        >
                                            ✕
                                        </button>
                                    )}
                                </div>
                            ))}
                        </div>

                        <div style={{ marginTop: '15px' }}>
                            <label style={{ display: 'block', marginBottom: '5px' }}>Tags (comma-separated)</label>
                            <input
                                type="text"
                                value={formData.tags}
                                onChange={(e) => setFormData({ ...formData, tags: e.target.value })}
                                placeholder="e.g., vegan, gluten-free, high-protein"
                                style={{ width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }}
                            />
                        </div>

                        <div style={{ marginTop: '15px' }}>
                            <label style={{ display: 'flex', alignItems: 'center', cursor: 'pointer' }}>
                                <input
                                    type="checkbox"
                                    checked={formData.is_public}
                                    onChange={(e) => setFormData({ ...formData, is_public: e.target.checked })}
                                    style={{ marginRight: '8px' }}
                                />
                                Make Public (visible to all clients)
                            </label>
                        </div>

                        <div style={{ display: 'flex', gap: '10px', marginTop: '20px' }}>
                            <button
                                type="submit"
                                style={{
                                    padding: '10px 20px',
                                    background: '#0073aa',
                                    color: '#fff',
                                    border: 'none',
                                    borderRadius: '4px',
                                    cursor: 'pointer'
                                }}
                            >
                                {editingId ? 'Update' : 'Create'} Recipe
                            </button>
                            <button
                                type="button"
                                onClick={resetForm}
                                style={{
                                    padding: '10px 20px',
                                    background: '#ddd',
                                    color: '#333',
                                    border: 'none',
                                    borderRadius: '4px',
                                    cursor: 'pointer'
                                }}
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            )}

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))', gap: '20px' }}>
                {recipes.length === 0 ? (
                    <div style={{ padding: '40px', textAlign: 'center', color: '#666' }}>
                        No recipes in library
                    </div>
                ) : (
                    recipes.map(recipe => (
                        <div key={recipe.id} style={{
                            background: '#fff',
                            border: '1px solid #ddd',
                            borderRadius: '4px',
                            padding: '20px'
                        }}>
                            <h3 style={{ margin: '0 0 10px 0' }}>{recipe.title}</h3>
                            <div style={{
                                fontSize: '12px',
                                color: '#fff',
                                background: '#666',
                                padding: '4px 8px',
                                borderRadius: '4px',
                                display: 'inline-block',
                                marginBottom: '10px'
                            }}>
                                {recipe.meal_type}
                            </div>
                            <p style={{ color: '#666', fontSize: '13px', marginBottom: '10px' }}>
                                {recipe.description || 'No description'}
                            </p>
                            <div style={{ fontSize: '12px', color: '#666', marginBottom: '10px' }}>
                                <div>🕐 Prep: {recipe.prep_time}m | Cook: {recipe.cook_time}m</div>
                                <div>🍽️ Servings: {recipe.servings}</div>
                                <div>⚡ {recipe.calories} cal | P: {recipe.protein}g | C: {recipe.carbs}g | F: {recipe.fats}g</div>
                            </div>
                            <div style={{ display: 'flex', gap: '10px' }}>
                                <button
                                    onClick={() => handleEdit(recipe)}
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
                                    Edit
                                </button>
                                <button
                                    onClick={() => handleDelete(recipe.id)}
                                    style={{
                                        padding: '6px 12px',
                                        background: '#dc3232',
                                        color: '#fff',
                                        border: 'none',
                                        borderRadius: '4px',
                                        cursor: 'pointer',
                                        fontSize: '12px'
                                    }}
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    ))
                )}
            </div>
        </div>
    );
};

export default Recipes;
