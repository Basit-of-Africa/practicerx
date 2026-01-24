import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const Documents = () => {
    const [documents, setDocuments] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadDocuments();
    }, []);

    const loadDocuments = async () => {
        try {
            const data = await apiFetch({ path: '/ppms/v1/documents' });
            setDocuments(data.data || []);
        } catch (error) {
            console.error('Error loading documents:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('Delete this document?')) return;
        try {
            await apiFetch({
                path: `/ppms/v1/documents/${id}`,
                method: 'DELETE'
            });
            loadDocuments();
        } catch (error) {
            alert('Error deleting document: ' + error.message);
        }
    };

    const formatFileSize = (bytes) => {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    };

    if (loading) return <div>Loading documents...</div>;

    return (
        <div>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '20px' }}>
                <h1>Document Library</h1>
                <button
                    onClick={() => alert('Document upload coming soon!')}
                    style={{
                        padding: '10px 20px',
                        background: '#0073aa',
                        color: '#fff',
                        border: 'none',
                        borderRadius: '4px',
                        cursor: 'pointer'
                    }}
                >
                    Upload Document
                </button>
            </div>

            <div style={{ background: '#fff', border: '1px solid #ddd', borderRadius: '4px', overflow: 'hidden' }}>
                <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                    <thead>
                        <tr style={{ background: '#f7f7f7' }}>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>Name</th>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>Type</th>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>Client</th>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>Size</th>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>Uploaded</th>
                            <th style={{ padding: '12px', textAlign: 'left', borderBottom: '1px solid #ddd' }}>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {documents.length === 0 ? (
                            <tr>
                                <td colSpan="6" style={{ padding: '20px', textAlign: 'center', color: '#666' }}>
                                    No documents uploaded
                                </td>
                            </tr>
                        ) : (
                            documents.map(doc => (
                                <tr key={doc.id} style={{ borderBottom: '1px solid #f0f0f0' }}>
                                    <td style={{ padding: '12px' }}>
                                        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                                            <span>📄</span>
                                            <span>{doc.file_name}</span>
                                        </div>
                                    </td>
                                    <td style={{ padding: '12px' }}>
                                        <span style={{
                                            padding: '4px 8px',
                                            background: '#f0f0f0',
                                            borderRadius: '4px',
                                            fontSize: '11px'
                                        }}>
                                            {doc.document_type}
                                        </span>
                                    </td>
                                    <td style={{ padding: '12px' }}>Client #{doc.client_id}</td>
                                    <td style={{ padding: '12px' }}>{formatFileSize(doc.file_size || 0)}</td>
                                    <td style={{ padding: '12px' }}>{doc.created_at}</td>
                                    <td style={{ padding: '12px' }}>
                                        <div style={{ display: 'flex', gap: '8px' }}>
                                            <a
                                                href={doc.file_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                style={{
                                                    padding: '4px 12px',
                                                    background: '#0073aa',
                                                    color: '#fff',
                                                    borderRadius: '4px',
                                                    textDecoration: 'none',
                                                    fontSize: '12px'
                                                }}
                                            >
                                                View
                                            </a>
                                            <button
                                                onClick={() => handleDelete(doc.id)}
                                                style={{
                                                    padding: '4px 12px',
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
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default Documents;
