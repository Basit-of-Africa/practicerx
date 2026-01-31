/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/components/Calendar.jsx"
/*!*************************************!*\
  !*** ./src/components/Calendar.jsx ***!
  \*************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);



const Calendar = () => {
  const [appointments, setAppointments] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)([]);
  const [loading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(true);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    const start = new Date().toISOString().split('T')[0];
    const end = new Date(new Date().setDate(new Date().getDate() + 30)).toISOString().split('T')[0];
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
      path: `/ppms/v1/appointments?start_date=${start}&end_date=${end}`
    }).then(data => setAppointments(data)).catch(error => console.error(error)).finally(() => setLoading(false));
  }, []);
  if (loading) return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "Loading calendar...");
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "practicerx-calendar"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", null, "Upcoming Appointments"), appointments.length === 0 ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, "No appointments found.") : (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("ul", null, appointments.map(appt => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
    key: appt.id
  }, appt.start_time, " - ", appt.status))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Calendar);

/***/ },

/***/ "./src/components/EncounterForm.jsx"
/*!******************************************!*\
  !*** ./src/components/EncounterForm.jsx ***!
  \******************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);



const EncounterForm = ({
  patientId,
  onSave
}) => {
  const [content, setContent] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)('');
  const [type, setType] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)('general');
  const [isSaving, setIsSaving] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(false);
  const handleSubmit = e => {
    e.preventDefault();
    setIsSaving(true);
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
      path: '/ppms/v1/encounters',
      method: 'POST',
      data: {
        patient_id: patientId,
        practitioner_id: window.practicerxSettings?.currentUserId || 1,
        type,
        content
      }
    }).then(response => {
      setContent('');
      if (onSave) onSave(response);
    }).catch(error => {
      console.error(error);
      alert('Failed to save encounter.');
    }).finally(() => {
      setIsSaving(false);
    });
  };
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "practicerx-encounter-form",
    style: {
      marginTop: '20px',
      padding: '15px',
      background: '#f9f9f9',
      border: '1px solid #ddd'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h4", null, "Add Clinical Note"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("form", {
    onSubmit: handleSubmit
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "form-group",
    style: {
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block'
    }
  }, "Type"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
    value: type,
    onChange: e => setType(e.target.value),
    style: {
      width: '100%'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "general"
  }, "General Note"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "soap"
  }, "SOAP Note"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "assessment"
  }, "Assessment"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "prescription"
  }, "Prescription"))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "form-group",
    style: {
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block'
    }
  }, "Content"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("textarea", {
    value: content,
    onChange: e => setContent(e.target.value),
    rows: "5",
    required: true,
    style: {
      width: '100%'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "submit",
    disabled: isSaving,
    className: "button button-primary"
  }, isSaving ? 'Saving...' : 'Save Note')));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (EncounterForm);

/***/ },

/***/ "./src/components/Layout.jsx"
/*!***********************************!*\
  !*** ./src/components/Layout.jsx ***!
  \***********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _utils_Router__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../utils/Router */ "./src/utils/Router.jsx");


const Layout = ({
  children
}) => {
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "practicerx-layout",
    style: {
      display: 'flex',
      minHeight: '100vh'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "practicerx-sidebar",
    style: {
      width: '200px',
      background: '#fff',
      borderRight: '1px solid #ddd',
      padding: '20px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", null, "PracticeRx"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("nav", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("ul", {
    style: {
      listStyle: 'none',
      padding: 0
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
    style: {
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_utils_Router__WEBPACK_IMPORTED_MODULE_1__.Link, {
    to: "/"
  }, "Dashboard")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
    style: {
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_utils_Router__WEBPACK_IMPORTED_MODULE_1__.Link, {
    to: "/appointments"
  }, "Appointments")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
    style: {
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_utils_Router__WEBPACK_IMPORTED_MODULE_1__.Link, {
    to: "/patients"
  }, "Clients")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
    style: {
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_utils_Router__WEBPACK_IMPORTED_MODULE_1__.Link, {
    to: "/telehealth"
  }, "Telehealth")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
    style: {
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_utils_Router__WEBPACK_IMPORTED_MODULE_1__.Link, {
    to: "/campaigns"
  }, "Campaigns")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
    style: {
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_utils_Router__WEBPACK_IMPORTED_MODULE_1__.Link, {
    to: "/recipes"
  }, "Recipes")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
    style: {
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_utils_Router__WEBPACK_IMPORTED_MODULE_1__.Link, {
    to: "/meal-plans"
  }, "Meal Plans")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
    style: {
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_utils_Router__WEBPACK_IMPORTED_MODULE_1__.Link, {
    to: "/forms"
  }, "Forms")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
    style: {
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_utils_Router__WEBPACK_IMPORTED_MODULE_1__.Link, {
    to: "/documents"
  }, "Documents")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
    style: {
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_utils_Router__WEBPACK_IMPORTED_MODULE_1__.Link, {
    to: "/health-tracking"
  }, "Health Tracking")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
    style: {
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_utils_Router__WEBPACK_IMPORTED_MODULE_1__.Link, {
    to: "/settings"
  }, "Settings"))))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "practicerx-content",
    style: {
      flex: 1,
      padding: '20px'
    }
  }, children));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Layout);

/***/ },

/***/ "./src/pages/Appointments.jsx"
/*!************************************!*\
  !*** ./src/pages/Appointments.jsx ***!
  \************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _components_Calendar__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../components/Calendar */ "./src/components/Calendar.jsx");


const Appointments = () => {
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "practicerx-page"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", null, "Appointments"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, "Manage your schedule here."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_Calendar__WEBPACK_IMPORTED_MODULE_1__["default"], null));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Appointments);

/***/ },

/***/ "./src/pages/Campaigns.jsx"
/*!*********************************!*\
  !*** ./src/pages/Campaigns.jsx ***!
  \*********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);



const Campaigns = () => {
  const [campaigns, setCampaigns] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)([]);
  const [loading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(true);
  const [showForm, setShowForm] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(false);
  const [editingId, setEditingId] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(null);
  const [formData, setFormData] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)({
    name: '',
    description: '',
    trigger_type: 'manual',
    trigger_event: '',
    emails: [{
      subject: '',
      body: '',
      delay_days: 0
    }],
    is_active: true
  });
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    loadCampaigns();
  }, []);
  const loadCampaigns = async () => {
    try {
      const data = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: '/ppms/v1/campaigns'
      });
      setCampaigns(data.data || []);
    } catch (error) {
      console.error('Error loading campaigns:', error);
    } finally {
      setLoading(false);
    }
  };
  const handleSubmit = async e => {
    e.preventDefault();
    try {
      if (editingId) {
        await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
          path: `/ppms/v1/campaigns/${editingId}`,
          method: 'PUT',
          data: formData
        });
      } else {
        await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
          path: '/ppms/v1/campaigns',
          method: 'POST',
          data: formData
        });
      }
      resetForm();
      loadCampaigns();
    } catch (error) {
      alert('Error saving campaign: ' + error.message);
    }
  };
  const handleEdit = campaign => {
    setFormData({
      name: campaign.name,
      description: campaign.description || '',
      trigger_type: campaign.trigger_type,
      trigger_event: campaign.trigger_event || '',
      emails: typeof campaign.emails === 'string' ? JSON.parse(campaign.emails) : campaign.emails,
      is_active: campaign.is_active === 1
    });
    setEditingId(campaign.id);
    setShowForm(true);
  };
  const handleDelete = async id => {
    if (!confirm('Delete this campaign?')) return;
    try {
      await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: `/ppms/v1/campaigns/${id}`,
        method: 'DELETE'
      });
      loadCampaigns();
    } catch (error) {
      alert('Error deleting campaign: ' + error.message);
    }
  };
  const resetForm = () => {
    setFormData({
      name: '',
      description: '',
      trigger_type: 'manual',
      trigger_event: '',
      emails: [{
        subject: '',
        body: '',
        delay_days: 0
      }],
      is_active: true
    });
    setEditingId(null);
    setShowForm(false);
  };
  const addEmailStep = () => {
    setFormData({
      ...formData,
      emails: [...formData.emails, {
        subject: '',
        body: '',
        delay_days: 0
      }]
    });
  };
  const removeEmailStep = index => {
    const newEmails = formData.emails.filter((_, i) => i !== index);
    setFormData({
      ...formData,
      emails: newEmails
    });
  };
  const updateEmailStep = (index, field, value) => {
    const newEmails = [...formData.emails];
    newEmails[index][field] = value;
    setFormData({
      ...formData,
      emails: newEmails
    });
  };
  if (loading) return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "Loading campaigns...");
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      marginBottom: '20px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", null, "Email Campaigns"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => setShowForm(!showForm),
    style: {
      padding: '10px 20px',
      background: '#0073aa',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer'
    }
  }, showForm ? 'Cancel' : 'New Campaign')), showForm && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      background: '#fff',
      padding: '20px',
      marginBottom: '20px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", null, editingId ? 'Edit Campaign' : 'Create Campaign'), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("form", {
    onSubmit: handleSubmit
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginBottom: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Campaign Name"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    value: formData.name,
    onChange: e => setFormData({
      ...formData,
      name: e.target.value
    }),
    required: true,
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginBottom: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Description"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("textarea", {
    value: formData.description,
    onChange: e => setFormData({
      ...formData,
      description: e.target.value
    }),
    rows: "3",
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginBottom: '15px',
      display: 'flex',
      gap: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      flex: 1
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Trigger Type"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
    value: formData.trigger_type,
    onChange: e => setFormData({
      ...formData,
      trigger_type: e.target.value
    }),
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "manual"
  }, "Manual"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "event"
  }, "Event-triggered"))), formData.trigger_type === 'event' && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      flex: 1
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Trigger Event"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
    value: formData.trigger_event,
    onChange: e => setFormData({
      ...formData,
      trigger_event: e.target.value
    }),
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: ""
  }, "Select event..."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "appointment_booked"
  }, "Appointment Booked"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "program_enrolled"
  }, "Program Enrolled"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "form_submitted"
  }, "Form Submitted")))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginBottom: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      fontWeight: 'bold'
    }
  }, "Email Sequence"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    onClick: addEmailStep,
    style: {
      padding: '6px 12px',
      background: '#00a32a',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '12px'
    }
  }, "+ Add Email")), formData.emails.map((email, index) => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    key: index,
    style: {
      padding: '15px',
      background: '#f9f9f9',
      border: '1px solid #e0e0e0',
      borderRadius: '4px',
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("strong", null, "Email ", index + 1), formData.emails.length > 1 && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    onClick: () => removeEmailStep(index),
    style: {
      padding: '4px 8px',
      background: '#dc3232',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '11px'
    }
  }, "Remove")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px',
      fontSize: '13px'
    }
  }, "Delay (days)"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "number",
    min: "0",
    value: email.delay_days,
    onChange: e => updateEmailStep(index, 'delay_days', parseInt(e.target.value)),
    style: {
      width: '100px',
      padding: '6px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px',
      fontSize: '13px'
    }
  }, "Subject"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    value: email.subject,
    onChange: e => updateEmailStep(index, 'subject', e.target.value),
    required: true,
    placeholder: "Use {{first_name}}, {{last_name}}, etc.",
    style: {
      width: '100%',
      padding: '6px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px',
      fontSize: '13px'
    }
  }, "Body"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("textarea", {
    value: email.body,
    onChange: e => updateEmailStep(index, 'body', e.target.value),
    required: true,
    rows: "4",
    placeholder: "Email content. Use merge tags: {{first_name}}, {{last_name}}, {{email}}, {{phone}}",
    style: {
      width: '100%',
      padding: '6px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }))))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginBottom: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'flex',
      alignItems: 'center',
      cursor: 'pointer'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "checkbox",
    checked: formData.is_active,
    onChange: e => setFormData({
      ...formData,
      is_active: e.target.checked
    }),
    style: {
      marginRight: '8px'
    }
  }), "Active")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      gap: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "submit",
    style: {
      padding: '10px 20px',
      background: '#0073aa',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer'
    }
  }, editingId ? 'Update' : 'Create', " Campaign"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    onClick: resetForm,
    style: {
      padding: '10px 20px',
      background: '#ddd',
      color: '#333',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer'
    }
  }, "Cancel")))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))',
      gap: '20px'
    }
  }, campaigns.length === 0 ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      padding: '40px',
      textAlign: 'center',
      color: '#666'
    }
  }, "No campaigns created yet") : campaigns.map(campaign => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    key: campaign.id,
    style: {
      background: '#fff',
      border: '1px solid #ddd',
      borderRadius: '4px',
      padding: '20px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'start',
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", {
    style: {
      margin: 0
    }
  }, campaign.name), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      padding: '4px 8px',
      borderRadius: '4px',
      fontSize: '11px',
      background: campaign.is_active ? '#00aa00' : '#999',
      color: '#fff'
    }
  }, campaign.is_active ? 'Active' : 'Inactive')), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      color: '#666',
      fontSize: '13px',
      marginBottom: '15px'
    }
  }, campaign.description || 'No description'), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      fontSize: '12px',
      color: '#666',
      marginBottom: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "Trigger: ", campaign.trigger_type), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "Emails: ", campaign.emails ? typeof campaign.emails === 'string' ? JSON.parse(campaign.emails).length : campaign.emails.length : 0)), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      gap: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => handleEdit(campaign),
    style: {
      padding: '6px 12px',
      background: '#0073aa',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '12px'
    }
  }, "Edit"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => handleDelete(campaign.id),
    style: {
      padding: '6px 12px',
      background: '#dc3232',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '12px'
    }
  }, "Delete"))))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Campaigns);

/***/ },

/***/ "./src/pages/Dashboard.jsx"
/*!*********************************!*\
  !*** ./src/pages/Dashboard.jsx ***!
  \*********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);



const Dashboard = () => {
  const [showDemoPrompt, setShowDemoPrompt] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(false);
  const [importing, setImporting] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(false);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
      path: '/ppms/v1/patients'
    }).then(data => {
      if (data.length === 0) setShowDemoPrompt(true);
    });
  }, []);
  const handleImport = () => {
    setImporting(true);
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
      path: '/ppms/v1/system/seed',
      method: 'POST'
    }).then(() => {
      setShowDemoPrompt(false);
      alert('Demo data imported successfully! Refresh the page to see it.');
    }).catch(error => {
      console.error(error);
      alert('Import failed.');
    }).finally(() => setImporting(false));
  };
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "practicerx-page"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", null, "Dashboard"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, "Welcome to PracticeRx."), showDemoPrompt && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "notice notice-info inline",
    style: {
      padding: '15px',
      margin: '20px 0'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", null, "Get Started Quickly"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, "It looks like this is a fresh installation. Would you like to import some demo data to explore the system?"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    className: "button button-primary",
    onClick: handleImport,
    disabled: importing
  }, importing ? 'Importing...' : 'Import Demo Data')));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Dashboard);

/***/ },

/***/ "./src/pages/Documents.jsx"
/*!*********************************!*\
  !*** ./src/pages/Documents.jsx ***!
  \*********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);



const Documents = () => {
  const [documents, setDocuments] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)([]);
  const [loading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(true);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    loadDocuments();
  }, []);
  const loadDocuments = async () => {
    try {
      const data = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: '/ppms/v1/documents'
      });
      setDocuments(data.data || []);
    } catch (error) {
      console.error('Error loading documents:', error);
    } finally {
      setLoading(false);
    }
  };
  const handleDelete = async id => {
    if (!confirm('Delete this document?')) return;
    try {
      await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: `/ppms/v1/documents/${id}`,
        method: 'DELETE'
      });
      loadDocuments();
    } catch (error) {
      alert('Error deleting document: ' + error.message);
    }
  };
  const formatFileSize = bytes => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
  };
  if (loading) return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "Loading documents...");
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      marginBottom: '20px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", null, "Document Library"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => alert('Document upload coming soon!'),
    style: {
      padding: '10px 20px',
      background: '#0073aa',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer'
    }
  }, "Upload Document")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      background: '#fff',
      border: '1px solid #ddd',
      borderRadius: '4px',
      overflow: 'hidden'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("table", {
    style: {
      width: '100%',
      borderCollapse: 'collapse'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("thead", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("tr", {
    style: {
      background: '#f7f7f7'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "Name"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "Type"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "Client"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "Size"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "Uploaded"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "Actions"))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("tbody", null, documents.length === 0 ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("tr", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    colSpan: "6",
    style: {
      padding: '20px',
      textAlign: 'center',
      color: '#666'
    }
  }, "No documents uploaded")) : documents.map(doc => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("tr", {
    key: doc.id,
    style: {
      borderBottom: '1px solid #f0f0f0'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: '8px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, "\uD83D\uDCC4"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, doc.file_name))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      padding: '4px 8px',
      background: '#f0f0f0',
      borderRadius: '4px',
      fontSize: '11px'
    }
  }, doc.document_type)), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px'
    }
  }, "Client #", doc.client_id), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px'
    }
  }, formatFileSize(doc.file_size || 0)), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px'
    }
  }, doc.created_at), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      gap: '8px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
    href: doc.file_url,
    target: "_blank",
    rel: "noopener noreferrer",
    style: {
      padding: '4px 12px',
      background: '#0073aa',
      color: '#fff',
      borderRadius: '4px',
      textDecoration: 'none',
      fontSize: '12px'
    }
  }, "View"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => handleDelete(doc.id),
    style: {
      padding: '4px 12px',
      background: '#dc3232',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '12px'
    }
  }, "Delete")))))))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Documents);

/***/ },

/***/ "./src/pages/Forms.jsx"
/*!*****************************!*\
  !*** ./src/pages/Forms.jsx ***!
  \*****************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);



const Forms = () => {
  const [forms, setForms] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)([]);
  const [loading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(true);
  const [showBuilder, setShowBuilder] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(false);
  const [editingId, setEditingId] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(null);
  const [formData, setFormData] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)({
    name: '',
    description: '',
    form_type: 'intake',
    fields: [{
      name: '',
      type: 'text',
      label: '',
      required: false,
      options: ''
    }]
  });
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    loadForms();
  }, []);
  const loadForms = async () => {
    try {
      const data = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: '/ppms/v1/forms'
      });
      setForms(data.data || []);
    } catch (error) {
      console.error('Error loading forms:', error);
    } finally {
      setLoading(false);
    }
  };
  const handleSubmit = async e => {
    e.preventDefault();
    try {
      const payload = {
        ...formData,
        practitioner_id: window.practicerxSettings?.currentUserId || 1
      };
      if (editingId) {
        await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
          path: `/ppms/v1/forms/${editingId}`,
          method: 'PUT',
          data: payload
        });
      } else {
        await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
          path: '/ppms/v1/forms',
          method: 'POST',
          data: payload
        });
      }
      resetForm();
      loadForms();
    } catch (error) {
      alert('Error saving form: ' + error.message);
    }
  };
  const handleEdit = form => {
    setFormData({
      name: form.name,
      description: form.description || '',
      form_type: form.form_type,
      fields: typeof form.fields === 'string' ? JSON.parse(form.fields) : form.fields
    });
    setEditingId(form.id);
    setShowBuilder(true);
  };
  const handleDelete = async id => {
    if (!confirm('Delete this form?')) return;
    try {
      await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: `/ppms/v1/forms/${id}`,
        method: 'DELETE'
      });
      loadForms();
    } catch (error) {
      alert('Error deleting form: ' + error.message);
    }
  };
  const resetForm = () => {
    setFormData({
      name: '',
      description: '',
      form_type: 'intake',
      fields: [{
        name: '',
        type: 'text',
        label: '',
        required: false,
        options: ''
      }]
    });
    setEditingId(null);
    setShowBuilder(false);
  };
  const addField = () => {
    setFormData({
      ...formData,
      fields: [...formData.fields, {
        name: '',
        type: 'text',
        label: '',
        required: false,
        options: ''
      }]
    });
  };
  const removeField = index => {
    const newFields = formData.fields.filter((_, i) => i !== index);
    setFormData({
      ...formData,
      fields: newFields
    });
  };
  const updateField = (index, key, value) => {
    const newFields = [...formData.fields];
    newFields[index][key] = value;
    setFormData({
      ...formData,
      fields: newFields
    });
  };
  if (loading) return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "Loading forms...");
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      marginBottom: '20px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", null, "Forms Builder"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => setShowBuilder(!showBuilder),
    style: {
      padding: '10px 20px',
      background: '#0073aa',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer'
    }
  }, showBuilder ? 'Cancel' : 'Create Form')), showBuilder && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      background: '#fff',
      padding: '20px',
      marginBottom: '20px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", null, editingId ? 'Edit Form' : 'Create Form'), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("form", {
    onSubmit: handleSubmit
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginBottom: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Form Name"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    value: formData.name,
    onChange: e => setFormData({
      ...formData,
      name: e.target.value
    }),
    required: true,
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginBottom: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Description"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("textarea", {
    value: formData.description,
    onChange: e => setFormData({
      ...formData,
      description: e.target.value
    }),
    rows: "2",
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginBottom: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Form Type"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
    value: formData.form_type,
    onChange: e => setFormData({
      ...formData,
      form_type: e.target.value
    }),
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "intake"
  }, "Intake Form"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "assessment"
  }, "Assessment"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "questionnaire"
  }, "Questionnaire"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "consent"
  }, "Consent Form"))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginBottom: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      fontWeight: 'bold'
    }
  }, "Form Fields"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    onClick: addField,
    style: {
      padding: '6px 12px',
      background: '#00a32a',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '12px'
    }
  }, "+ Add Field")), formData.fields.map((field, index) => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    key: index,
    style: {
      padding: '15px',
      background: '#f9f9f9',
      border: '1px solid #e0e0e0',
      borderRadius: '4px',
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("strong", null, "Field ", index + 1), formData.fields.length > 1 && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    onClick: () => removeField(index),
    style: {
      padding: '4px 8px',
      background: '#dc3232',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '11px'
    }
  }, "Remove")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: '1fr 1fr',
      gap: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px',
      fontSize: '13px'
    }
  }, "Field Name"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    value: field.name,
    onChange: e => updateField(index, 'name', e.target.value),
    required: true,
    placeholder: "e.g., first_name",
    style: {
      width: '100%',
      padding: '6px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px',
      fontSize: '13px'
    }
  }, "Field Type"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
    value: field.type,
    onChange: e => updateField(index, 'type', e.target.value),
    style: {
      width: '100%',
      padding: '6px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "text"
  }, "Text"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "email"
  }, "Email"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "number"
  }, "Number"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "textarea"
  }, "Textarea"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "select"
  }, "Dropdown"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "radio"
  }, "Radio"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "checkbox"
  }, "Checkbox"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "date"
  }, "Date")))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginTop: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px',
      fontSize: '13px'
    }
  }, "Label"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    value: field.label,
    onChange: e => updateField(index, 'label', e.target.value),
    required: true,
    placeholder: "Display label for field",
    style: {
      width: '100%',
      padding: '6px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), ['select', 'radio', 'checkbox'].includes(field.type) && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginTop: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px',
      fontSize: '13px'
    }
  }, "Options (comma-separated)"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    value: field.options,
    onChange: e => updateField(index, 'options', e.target.value),
    placeholder: "e.g., Option 1, Option 2, Option 3",
    style: {
      width: '100%',
      padding: '6px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginTop: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'flex',
      alignItems: 'center',
      cursor: 'pointer'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "checkbox",
    checked: field.required,
    onChange: e => updateField(index, 'required', e.target.checked),
    style: {
      marginRight: '8px'
    }
  }), "Required field"))))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      gap: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "submit",
    style: {
      padding: '10px 20px',
      background: '#0073aa',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer'
    }
  }, editingId ? 'Update' : 'Create', " Form"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    onClick: resetForm,
    style: {
      padding: '10px 20px',
      background: '#ddd',
      color: '#333',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer'
    }
  }, "Cancel")))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))',
      gap: '20px'
    }
  }, forms.length === 0 ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      padding: '40px',
      textAlign: 'center',
      color: '#666'
    }
  }, "No forms created yet") : forms.map(form => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    key: form.id,
    style: {
      background: '#fff',
      border: '1px solid #ddd',
      borderRadius: '4px',
      padding: '20px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", {
    style: {
      margin: '0 0 10px 0'
    }
  }, form.name), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      fontSize: '12px',
      color: '#fff',
      background: '#666',
      padding: '4px 8px',
      borderRadius: '4px',
      display: 'inline-block',
      marginBottom: '10px'
    }
  }, form.form_type), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      color: '#666',
      fontSize: '13px',
      marginBottom: '10px'
    }
  }, form.description || 'No description'), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      fontSize: '12px',
      color: '#666',
      marginBottom: '15px'
    }
  }, "Fields: ", form.fields ? typeof form.fields === 'string' ? JSON.parse(form.fields).length : form.fields.length : 0), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      gap: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => handleEdit(form),
    style: {
      padding: '6px 12px',
      background: '#0073aa',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '12px'
    }
  }, "Edit"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => handleDelete(form.id),
    style: {
      padding: '6px 12px',
      background: '#dc3232',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '12px'
    }
  }, "Delete"))))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Forms);

/***/ },

/***/ "./src/pages/HealthTracking.jsx"
/*!**************************************!*\
  !*** ./src/pages/HealthTracking.jsx ***!
  \**************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);



const HealthTracking = () => {
  const [metrics, setMetrics] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)([]);
  const [loading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(true);
  const [showForm, setShowForm] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(false);
  const [formData, setFormData] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)({
    client_id: '',
    metric_type: 'weight',
    value: '',
    unit: 'kg',
    notes: ''
  });
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    loadMetrics();
  }, []);
  const loadMetrics = async () => {
    try {
      const data = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: '/ppms/v1/health-metrics'
      });
      setMetrics(data.data || []);
    } catch (error) {
      console.error('Error loading metrics:', error);
    } finally {
      setLoading(false);
    }
  };
  const handleSubmit = async e => {
    e.preventDefault();
    try {
      await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: '/ppms/v1/health-metrics',
        method: 'POST',
        data: formData
      });
      setShowForm(false);
      setFormData({
        client_id: '',
        metric_type: 'weight',
        value: '',
        unit: 'kg',
        notes: ''
      });
      loadMetrics();
    } catch (error) {
      alert('Error recording metric: ' + error.message);
    }
  };
  const handleDelete = async id => {
    if (!confirm('Delete this metric?')) return;
    try {
      await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: `/ppms/v1/health-metrics/${id}`,
        method: 'DELETE'
      });
      loadMetrics();
    } catch (error) {
      alert('Error deleting metric: ' + error.message);
    }
  };
  const getMetricIcon = type => {
    const icons = {
      weight: '⚖️',
      blood_pressure: '💉',
      heart_rate: '❤️',
      temperature: '🌡️',
      blood_sugar: '🩸',
      cholesterol: '📊',
      other: '📈'
    };
    return icons[type] || '📊';
  };
  if (loading) return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "Loading health metrics...");
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      marginBottom: '20px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", null, "Health Tracking"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => setShowForm(!showForm),
    style: {
      padding: '10px 20px',
      background: '#0073aa',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer'
    }
  }, showForm ? 'Cancel' : 'Record Metric')), showForm && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      background: '#fff',
      padding: '20px',
      marginBottom: '20px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", null, "Record Health Metric"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("form", {
    onSubmit: handleSubmit
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: '1fr 1fr',
      gap: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Client ID"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "number",
    value: formData.client_id,
    onChange: e => setFormData({
      ...formData,
      client_id: e.target.value
    }),
    required: true,
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Metric Type"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
    value: formData.metric_type,
    onChange: e => setFormData({
      ...formData,
      metric_type: e.target.value
    }),
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "weight"
  }, "Weight"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "blood_pressure"
  }, "Blood Pressure"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "heart_rate"
  }, "Heart Rate"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "temperature"
  }, "Temperature"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "blood_sugar"
  }, "Blood Sugar"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "cholesterol"
  }, "Cholesterol"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "other"
  }, "Other")))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: '2fr 1fr',
      gap: '15px',
      marginTop: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Value"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    value: formData.value,
    onChange: e => setFormData({
      ...formData,
      value: e.target.value
    }),
    required: true,
    placeholder: "e.g., 70 or 120/80",
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Unit"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    value: formData.unit,
    onChange: e => setFormData({
      ...formData,
      unit: e.target.value
    }),
    placeholder: "e.g., kg, mmHg",
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginTop: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Notes (optional)"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("textarea", {
    value: formData.notes,
    onChange: e => setFormData({
      ...formData,
      notes: e.target.value
    }),
    rows: "3",
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "submit",
    style: {
      marginTop: '15px',
      padding: '10px 20px',
      background: '#0073aa',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer'
    }
  }, "Record Metric"))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      background: '#fff',
      border: '1px solid #ddd',
      borderRadius: '4px',
      overflow: 'hidden'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("table", {
    style: {
      width: '100%',
      borderCollapse: 'collapse'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("thead", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("tr", {
    style: {
      background: '#f7f7f7'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "Type"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "Client"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "Value"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "Unit"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "Recorded"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "Actions"))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("tbody", null, metrics.length === 0 ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("tr", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    colSpan: "6",
    style: {
      padding: '20px',
      textAlign: 'center',
      color: '#666'
    }
  }, "No health metrics recorded")) : metrics.map(metric => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("tr", {
    key: metric.id,
    style: {
      borderBottom: '1px solid #f0f0f0'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      alignItems: 'center',
      gap: '8px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, getMetricIcon(metric.metric_type)), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", null, metric.metric_type.replace('_', ' ')))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px'
    }
  }, "Client #", metric.client_id), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px',
      fontWeight: 'bold'
    }
  }, metric.value), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px'
    }
  }, metric.unit), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px'
    }
  }, metric.recorded_at), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => handleDelete(metric.id),
    style: {
      padding: '4px 12px',
      background: '#dc3232',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '12px'
    }
  }, "Delete"))))))), metrics.length > 0 && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginTop: '20px',
      padding: '20px',
      background: '#fff',
      border: '1px solid #ddd',
      borderRadius: '4px',
      textAlign: 'center',
      color: '#666'
    }
  }, "\uD83D\uDCCA Interactive charts and trend analysis coming soon"));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (HealthTracking);

/***/ },

/***/ "./src/pages/MealPlans.jsx"
/*!*********************************!*\
  !*** ./src/pages/MealPlans.jsx ***!
  \*********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);



const MealPlans = () => {
  const [plans, setPlans] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)([]);
  const [loading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(true);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    loadPlans();
  }, []);
  const loadPlans = async () => {
    try {
      const data = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: '/ppms/v1/meal-plans'
      });
      setPlans(data.data || []);
    } catch (error) {
      console.error('Error loading meal plans:', error);
    } finally {
      setLoading(false);
    }
  };
  if (loading) return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "Loading meal plans...");
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      marginBottom: '20px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", null, "Meal Plans"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => alert('Meal plan builder coming soon!'),
    style: {
      padding: '10px 20px',
      background: '#0073aa',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer'
    }
  }, "Create Meal Plan")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: 'repeat(auto-fill, minmax(350px, 1fr))',
      gap: '20px'
    }
  }, plans.length === 0 ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      padding: '40px',
      textAlign: 'center',
      color: '#666'
    }
  }, "No meal plans created") : plans.map(plan => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    key: plan.id,
    style: {
      background: '#fff',
      border: '1px solid #ddd',
      borderRadius: '4px',
      padding: '20px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", {
    style: {
      margin: '0 0 10px 0'
    }
  }, plan.name), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      color: '#666',
      fontSize: '13px',
      marginBottom: '15px'
    }
  }, plan.description || 'No description'), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      fontSize: '12px',
      color: '#666',
      marginBottom: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "Duration: ", plan.duration_days, " days"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "Target: ", plan.calories_target || 0, " calories/day"), plan.is_template === 1 && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginTop: '5px',
      padding: '4px 8px',
      background: '#0073aa',
      color: '#fff',
      borderRadius: '4px',
      display: 'inline-block'
    }
  }, "Template")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => alert('View plan details coming soon!'),
    style: {
      padding: '6px 12px',
      background: '#0073aa',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '12px'
    }
  }, "View Plan")))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (MealPlans);

/***/ },

/***/ "./src/pages/PatientDetail.jsx"
/*!*************************************!*\
  !*** ./src/pages/PatientDetail.jsx ***!
  \*************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _components_EncounterForm__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../components/EncounterForm */ "./src/components/EncounterForm.jsx");




const PatientDetail = ({
  id
}) => {
  const [patient, setPatient] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(null);
  const [encounters, setEncounters] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)([]);
  const [loading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(true);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    Promise.all([_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
      path: `/ppms/v1/patients/${id}`
    }), _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
      path: `/ppms/v1/patients/${id}/encounters`
    })]).then(results => {
      setPatient(results[0]);
      setEncounters(results[1]);
    }).catch(error => {
      console.error(error);
    }).finally(() => {
      setLoading(false);
    });
  }, [id]);
  const handleEncounterSaved = newEncounter => {
    setEncounters([newEncounter, ...encounters]);
  };
  if (loading) return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "Loading patient details...");
  if (!patient) return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "Patient not found.");
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "practicerx-page practicerx-patient-detail"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "patient-header"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", null, "Patient #", patient.id), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "patient-info"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("strong", null, "Phone:"), " ", patient.phone), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("strong", null, "Gender:"), " ", patient.gender), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("strong", null, "DOB:"), " ", patient.dob))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "patient-content"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "encounters-section"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", null, "Clinical History"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_EncounterForm__WEBPACK_IMPORTED_MODULE_3__["default"], {
    patientId: patient.id,
    onSave: handleEncounterSaved
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "encounters-list",
    style: {
      marginTop: '20px'
    }
  }, encounters.map(encounter => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    key: encounter.id,
    className: "encounter-card",
    style: {
      background: '#fff',
      border: '1px solid #ddd',
      padding: '15px',
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "encounter-meta",
    style: {
      borderBottom: '1px solid #eee',
      paddingBottom: '5px',
      marginBottom: '5px',
      fontSize: '0.9em',
      color: '#666'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "type",
    style: {
      textTransform: 'uppercase',
      fontWeight: 'bold',
      marginRight: '10px'
    }
  }, encounter.type), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "date"
  }, new Date(encounter.created_at).toLocaleDateString())), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "encounter-body",
    style: {
      whiteSpace: 'pre-wrap'
    }
  }, encounter.content)))))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (PatientDetail);

/***/ },

/***/ "./src/pages/PatientList.jsx"
/*!***********************************!*\
  !*** ./src/pages/PatientList.jsx ***!
  \***********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _utils_Router__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../utils/Router */ "./src/utils/Router.jsx");




const PatientList = () => {
  const [patients, setPatients] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)([]);
  const [loading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(true);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
      path: '/ppms/v1/patients'
    }).then(data => setPatients(data)).catch(error => console.error(error)).finally(() => setLoading(false));
  }, []);
  if (loading) return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "Loading patients...");
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "practicerx-patient-list"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("table", {
    className: "wp-list-table widefat fixed striped"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("thead", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("tr", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", null, "ID"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", null, "Phone"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", null, "Gender"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", null, "Actions"))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("tbody", null, patients.map(patient => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("tr", {
    key: patient.id
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", null, patient.id), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", null, patient.phone), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", null, patient.gender), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_utils_Router__WEBPACK_IMPORTED_MODULE_3__.Link, {
    to: `/patients/${patient.id}`
  }, "View")))))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (PatientList);

/***/ },

/***/ "./src/pages/Patients.jsx"
/*!********************************!*\
  !*** ./src/pages/Patients.jsx ***!
  \********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _PatientList__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./PatientList */ "./src/pages/PatientList.jsx");


const Patients = () => {
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "practicerx-page"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", null, "Patients"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, "View and manage patient records."), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_PatientList__WEBPACK_IMPORTED_MODULE_1__["default"], null));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Patients);

/***/ },

/***/ "./src/pages/Recipes.jsx"
/*!*******************************!*\
  !*** ./src/pages/Recipes.jsx ***!
  \*******************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);



const Recipes = () => {
  const [recipes, setRecipes] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)([]);
  const [loading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(true);
  const [showForm, setShowForm] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(false);
  const [editingId, setEditingId] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(null);
  const [formData, setFormData] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)({
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
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    loadRecipes();
  }, []);
  const loadRecipes = async () => {
    try {
      const data = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: '/ppms/v1/recipes'
      });
      setRecipes(data.data || []);
    } catch (error) {
      console.error('Error loading recipes:', error);
    } finally {
      setLoading(false);
    }
  };
  const handleSubmit = async e => {
    e.preventDefault();
    try {
      const payload = {
        ...formData,
        practitioner_id: window.practicerxSettings?.currentUserId || 1
      };
      if (editingId) {
        await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
          path: `/ppms/v1/recipes/${editingId}`,
          method: 'PUT',
          data: payload
        });
      } else {
        await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
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
  const handleEdit = recipe => {
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
  const handleDelete = async id => {
    if (!confirm('Delete this recipe?')) return;
    try {
      await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
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
    setFormData({
      ...formData,
      ingredients: [...formData.ingredients, '']
    });
  };
  const removeIngredient = index => {
    const newIngredients = formData.ingredients.filter((_, i) => i !== index);
    setFormData({
      ...formData,
      ingredients: newIngredients
    });
  };
  const updateIngredient = (index, value) => {
    const newIngredients = [...formData.ingredients];
    newIngredients[index] = value;
    setFormData({
      ...formData,
      ingredients: newIngredients
    });
  };
  const addInstruction = () => {
    setFormData({
      ...formData,
      instructions: [...formData.instructions, '']
    });
  };
  const removeInstruction = index => {
    const newInstructions = formData.instructions.filter((_, i) => i !== index);
    setFormData({
      ...formData,
      instructions: newInstructions
    });
  };
  const updateInstruction = (index, value) => {
    const newInstructions = [...formData.instructions];
    newInstructions[index] = value;
    setFormData({
      ...formData,
      instructions: newInstructions
    });
  };
  if (loading) return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "Loading recipes...");
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      marginBottom: '20px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", null, "Recipe Library"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => setShowForm(!showForm),
    style: {
      padding: '10px 20px',
      background: '#0073aa',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer'
    }
  }, showForm ? 'Cancel' : 'Add Recipe')), showForm && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      background: '#fff',
      padding: '20px',
      marginBottom: '20px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", null, editingId ? 'Edit Recipe' : 'Add Recipe'), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("form", {
    onSubmit: handleSubmit
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: '1fr 1fr',
      gap: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Recipe Title"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    value: formData.title,
    onChange: e => setFormData({
      ...formData,
      title: e.target.value
    }),
    required: true,
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Meal Type"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
    value: formData.meal_type,
    onChange: e => setFormData({
      ...formData,
      meal_type: e.target.value
    }),
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "breakfast"
  }, "Breakfast"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "lunch"
  }, "Lunch"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "dinner"
  }, "Dinner"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "snack"
  }, "Snack")))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginTop: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Description"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("textarea", {
    value: formData.description,
    onChange: e => setFormData({
      ...formData,
      description: e.target.value
    }),
    rows: "2",
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: '1fr 1fr 1fr',
      gap: '15px',
      marginTop: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Prep Time (min)"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "number",
    value: formData.prep_time,
    onChange: e => setFormData({
      ...formData,
      prep_time: parseInt(e.target.value)
    }),
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Cook Time (min)"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "number",
    value: formData.cook_time,
    onChange: e => setFormData({
      ...formData,
      cook_time: parseInt(e.target.value)
    }),
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Servings"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "number",
    value: formData.servings,
    onChange: e => setFormData({
      ...formData,
      servings: parseInt(e.target.value)
    }),
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: '1fr 1fr 1fr 1fr',
      gap: '15px',
      marginTop: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Calories"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "number",
    value: formData.calories,
    onChange: e => setFormData({
      ...formData,
      calories: parseInt(e.target.value)
    }),
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Protein (g)"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "number",
    step: "0.1",
    value: formData.protein,
    onChange: e => setFormData({
      ...formData,
      protein: parseFloat(e.target.value)
    }),
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Carbs (g)"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "number",
    step: "0.1",
    value: formData.carbs,
    onChange: e => setFormData({
      ...formData,
      carbs: parseFloat(e.target.value)
    }),
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Fats (g)"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "number",
    step: "0.1",
    value: formData.fats,
    onChange: e => setFormData({
      ...formData,
      fats: parseFloat(e.target.value)
    }),
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginTop: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      fontWeight: 'bold'
    }
  }, "Ingredients"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    onClick: addIngredient,
    style: {
      padding: '6px 12px',
      background: '#00a32a',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '12px'
    }
  }, "+ Add")), formData.ingredients.map((ingredient, index) => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    key: index,
    style: {
      display: 'flex',
      gap: '10px',
      marginBottom: '8px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    value: ingredient,
    onChange: e => updateIngredient(index, e.target.value),
    placeholder: "e.g., 1 cup flour",
    style: {
      flex: 1,
      padding: '6px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }), formData.ingredients.length > 1 && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    onClick: () => removeIngredient(index),
    style: {
      padding: '6px 12px',
      background: '#dc3232',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer'
    }
  }, "\u2715")))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginTop: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      fontWeight: 'bold'
    }
  }, "Instructions"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    onClick: addInstruction,
    style: {
      padding: '6px 12px',
      background: '#00a32a',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '12px'
    }
  }, "+ Add")), formData.instructions.map((instruction, index) => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    key: index,
    style: {
      display: 'flex',
      gap: '10px',
      marginBottom: '8px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    style: {
      padding: '6px 10px',
      background: '#f0f0f0',
      borderRadius: '4px'
    }
  }, index + 1), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    value: instruction,
    onChange: e => updateInstruction(index, e.target.value),
    placeholder: "Step instructions",
    style: {
      flex: 1,
      padding: '6px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }), formData.instructions.length > 1 && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    onClick: () => removeInstruction(index),
    style: {
      padding: '6px 12px',
      background: '#dc3232',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer'
    }
  }, "\u2715")))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginTop: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Tags (comma-separated)"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    value: formData.tags,
    onChange: e => setFormData({
      ...formData,
      tags: e.target.value
    }),
    placeholder: "e.g., vegan, gluten-free, high-protein",
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginTop: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'flex',
      alignItems: 'center',
      cursor: 'pointer'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "checkbox",
    checked: formData.is_public,
    onChange: e => setFormData({
      ...formData,
      is_public: e.target.checked
    }),
    style: {
      marginRight: '8px'
    }
  }), "Make Public (visible to all clients)")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      gap: '10px',
      marginTop: '20px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "submit",
    style: {
      padding: '10px 20px',
      background: '#0073aa',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer'
    }
  }, editingId ? 'Update' : 'Create', " Recipe"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    onClick: resetForm,
    style: {
      padding: '10px 20px',
      background: '#ddd',
      color: '#333',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer'
    }
  }, "Cancel")))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'grid',
      gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))',
      gap: '20px'
    }
  }, recipes.length === 0 ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      padding: '40px',
      textAlign: 'center',
      color: '#666'
    }
  }, "No recipes in library") : recipes.map(recipe => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    key: recipe.id,
    style: {
      background: '#fff',
      border: '1px solid #ddd',
      borderRadius: '4px',
      padding: '20px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h3", {
    style: {
      margin: '0 0 10px 0'
    }
  }, recipe.title), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      fontSize: '12px',
      color: '#fff',
      background: '#666',
      padding: '4px 8px',
      borderRadius: '4px',
      display: 'inline-block',
      marginBottom: '10px'
    }
  }, recipe.meal_type), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    style: {
      color: '#666',
      fontSize: '13px',
      marginBottom: '10px'
    }
  }, recipe.description || 'No description'), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      fontSize: '12px',
      color: '#666',
      marginBottom: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "\uD83D\uDD50 Prep: ", recipe.prep_time, "m | Cook: ", recipe.cook_time, "m"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "\uD83C\uDF7D\uFE0F Servings: ", recipe.servings), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "\u26A1 ", recipe.calories, " cal | P: ", recipe.protein, "g | C: ", recipe.carbs, "g | F: ", recipe.fats, "g")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      gap: '10px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => handleEdit(recipe),
    style: {
      padding: '6px 12px',
      background: '#0073aa',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '12px'
    }
  }, "Edit"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => handleDelete(recipe.id),
    style: {
      padding: '6px 12px',
      background: '#dc3232',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '12px'
    }
  }, "Delete"))))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Recipes);

/***/ },

/***/ "./src/pages/Settings.jsx"
/*!********************************!*\
  !*** ./src/pages/Settings.jsx ***!
  \********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);

const Settings = () => {
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "practicerx-page"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", null, "Settings"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, "Configure your practice settings."));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Settings);

/***/ },

/***/ "./src/pages/Telehealth.jsx"
/*!**********************************!*\
  !*** ./src/pages/Telehealth.jsx ***!
  \**********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);



const Telehealth = () => {
  const [sessions, setSessions] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)([]);
  const [loading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(true);
  const [showForm, setShowForm] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(false);
  const [formData, setFormData] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)({
    client_id: '',
    practitioner_id: '',
    appointment_id: '',
    provider: 'zoom',
    scheduled_for: ''
  });
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    loadSessions();
  }, []);
  const loadSessions = async () => {
    try {
      const data = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: '/ppms/v1/telehealth/sessions'
      });
      setSessions(data.data || []);
    } catch (error) {
      console.error('Error loading sessions:', error);
    } finally {
      setLoading(false);
    }
  };
  const handleSubmit = async e => {
    e.preventDefault();
    try {
      await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: '/ppms/v1/telehealth/sessions',
        method: 'POST',
        data: formData
      });
      setShowForm(false);
      setFormData({
        client_id: '',
        practitioner_id: '',
        appointment_id: '',
        provider: 'zoom',
        scheduled_for: ''
      });
      loadSessions();
    } catch (error) {
      alert('Error creating session: ' + error.message);
    }
  };
  const handleEndSession = async id => {
    if (!confirm('End this session?')) return;
    try {
      await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
        path: `/ppms/v1/telehealth/sessions/${id}/end`,
        method: 'POST'
      });
      loadSessions();
    } catch (error) {
      alert('Error ending session: ' + error.message);
    }
  };
  const getStatusBadge = status => {
    const colors = {
      scheduled: '#ffa500',
      'in-progress': '#00aa00',
      completed: '#0066cc',
      cancelled: '#cc0000'
    };
    return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
      style: {
        padding: '4px 8px',
        borderRadius: '4px',
        fontSize: '12px',
        color: '#fff',
        background: colors[status] || '#666'
      }
    }, status);
  };
  if (loading) return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "Loading sessions...");
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      display: 'flex',
      justifyContent: 'space-between',
      marginBottom: '20px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", null, "Telehealth Sessions"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => setShowForm(!showForm),
    style: {
      padding: '10px 20px',
      background: '#0073aa',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer'
    }
  }, showForm ? 'Cancel' : 'Schedule Session')), showForm && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      background: '#fff',
      padding: '20px',
      marginBottom: '20px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", null, "Schedule Video Session"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("form", {
    onSubmit: handleSubmit
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginBottom: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Client ID"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "number",
    value: formData.client_id,
    onChange: e => setFormData({
      ...formData,
      client_id: e.target.value
    }),
    required: true,
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginBottom: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Practitioner ID"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "number",
    value: formData.practitioner_id,
    onChange: e => setFormData({
      ...formData,
      practitioner_id: e.target.value
    }),
    required: true,
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginBottom: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Appointment ID (optional)"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "number",
    value: formData.appointment_id,
    onChange: e => setFormData({
      ...formData,
      appointment_id: e.target.value
    }),
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginBottom: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Provider"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
    value: formData.provider,
    onChange: e => setFormData({
      ...formData,
      provider: e.target.value
    }),
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "zoom"
  }, "Zoom"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: "twilio"
  }, "Twilio Video"))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      marginBottom: '15px'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    style: {
      display: 'block',
      marginBottom: '5px'
    }
  }, "Scheduled For"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "datetime-local",
    value: formData.scheduled_for,
    onChange: e => setFormData({
      ...formData,
      scheduled_for: e.target.value
    }),
    required: true,
    style: {
      width: '100%',
      padding: '8px',
      border: '1px solid #ddd',
      borderRadius: '4px'
    }
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "submit",
    style: {
      padding: '10px 20px',
      background: '#0073aa',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer'
    }
  }, "Create Session"))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    style: {
      background: '#fff',
      border: '1px solid #ddd',
      borderRadius: '4px',
      overflow: 'hidden'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("table", {
    style: {
      width: '100%',
      borderCollapse: 'collapse'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("thead", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("tr", {
    style: {
      background: '#f7f7f7'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "ID"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "Client"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "Provider"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "Scheduled"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "Status"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("th", {
    style: {
      padding: '12px',
      textAlign: 'left',
      borderBottom: '1px solid #ddd'
    }
  }, "Actions"))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("tbody", null, sessions.length === 0 ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("tr", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    colSpan: "6",
    style: {
      padding: '20px',
      textAlign: 'center',
      color: '#666'
    }
  }, "No sessions scheduled")) : sessions.map(session => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("tr", {
    key: session.id,
    style: {
      borderBottom: '1px solid #f0f0f0'
    }
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px'
    }
  }, session.id), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px'
    }
  }, "Client #", session.client_id), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px'
    }
  }, session.provider), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px'
    }
  }, session.scheduled_for), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px'
    }
  }, getStatusBadge(session.status)), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("td", {
    style: {
      padding: '12px'
    }
  }, session.join_url && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
    href: session.join_url,
    target: "_blank",
    rel: "noopener noreferrer",
    style: {
      marginRight: '10px',
      color: '#0073aa',
      textDecoration: 'none'
    }
  }, "Join"), session.status === 'in-progress' && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    onClick: () => handleEndSession(session.id),
    style: {
      padding: '4px 12px',
      background: '#dc3232',
      color: '#fff',
      border: 'none',
      borderRadius: '4px',
      cursor: 'pointer',
      fontSize: '12px'
    }
  }, "End"))))))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Telehealth);

/***/ },

/***/ "./src/utils/Router.jsx"
/*!******************************!*\
  !*** ./src/utils/Router.jsx ***!
  \******************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Link: () => (/* binding */ Link),
/* harmony export */   Router: () => (/* binding */ Router),
/* harmony export */   RouterContext: () => (/* binding */ RouterContext)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);


const RouterContext = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createContext)({
  path: '/',
  navigate: () => {}
});
const Router = ({
  children
}) => {
  const [path, setPath] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(window.location.hash.substr(1) || '/');
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    const handleHashChange = () => {
      setPath(window.location.hash.substr(1) || '/');
    };
    window.addEventListener('hashchange', handleHashChange);
    return () => {
      window.removeEventListener('hashchange', handleHashChange);
    };
  }, []);
  const navigate = newPath => {
    window.location.hash = newPath;
  };
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(RouterContext.Provider, {
    value: {
      path,
      navigate
    }
  }, children);
};
const Link = ({
  to,
  children,
  className
}) => {
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
    href: `#${to}`,
    className: className,
    style: {
      cursor: 'pointer',
      color: '#0073aa',
      textDecoration: 'none'
    }
  }, children);
};

/***/ },

/***/ "@wordpress/api-fetch"
/*!**********************************!*\
  !*** external ["wp","apiFetch"] ***!
  \**********************************/
(module) {

module.exports = window["wp"]["apiFetch"];

/***/ },

/***/ "@wordpress/element"
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
(module) {

module.exports = window["wp"]["element"];

/***/ },

/***/ "react"
/*!************************!*\
  !*** external "React" ***!
  \************************/
(module) {

module.exports = window["React"];

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Check if module exists (development only)
/******/ 		if (__webpack_modules__[moduleId] === undefined) {
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _utils_Router__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./utils/Router */ "./src/utils/Router.jsx");
/* harmony import */ var _components_Layout__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/Layout */ "./src/components/Layout.jsx");
/* harmony import */ var _pages_Dashboard__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./pages/Dashboard */ "./src/pages/Dashboard.jsx");
/* harmony import */ var _pages_Appointments__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./pages/Appointments */ "./src/pages/Appointments.jsx");
/* harmony import */ var _pages_Patients__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./pages/Patients */ "./src/pages/Patients.jsx");
/* harmony import */ var _pages_PatientDetail__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./pages/PatientDetail */ "./src/pages/PatientDetail.jsx");
/* harmony import */ var _pages_Settings__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./pages/Settings */ "./src/pages/Settings.jsx");
/* harmony import */ var _pages_Telehealth__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./pages/Telehealth */ "./src/pages/Telehealth.jsx");
/* harmony import */ var _pages_Campaigns__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./pages/Campaigns */ "./src/pages/Campaigns.jsx");
/* harmony import */ var _pages_Recipes__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./pages/Recipes */ "./src/pages/Recipes.jsx");
/* harmony import */ var _pages_MealPlans__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./pages/MealPlans */ "./src/pages/MealPlans.jsx");
/* harmony import */ var _pages_Forms__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! ./pages/Forms */ "./src/pages/Forms.jsx");
/* harmony import */ var _pages_Documents__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! ./pages/Documents */ "./src/pages/Documents.jsx");
/* harmony import */ var _pages_HealthTracking__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! ./pages/HealthTracking */ "./src/pages/HealthTracking.jsx");
















const App = () => {
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_utils_Router__WEBPACK_IMPORTED_MODULE_2__.Router, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_utils_Router__WEBPACK_IMPORTED_MODULE_2__.RouterContext.Consumer, null, ({
    path
  }) => {
    let content;
    if (path === '/') content = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_pages_Dashboard__WEBPACK_IMPORTED_MODULE_4__["default"], null);else if (path === '/appointments') content = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_pages_Appointments__WEBPACK_IMPORTED_MODULE_5__["default"], null);else if (path === '/patients') content = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_pages_Patients__WEBPACK_IMPORTED_MODULE_6__["default"], null);else if (path.startsWith('/patients/')) content = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_pages_PatientDetail__WEBPACK_IMPORTED_MODULE_7__["default"], {
      id: path.split('/')[2]
    });else if (path === '/telehealth') content = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_pages_Telehealth__WEBPACK_IMPORTED_MODULE_9__["default"], null);else if (path === '/campaigns') content = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_pages_Campaigns__WEBPACK_IMPORTED_MODULE_10__["default"], null);else if (path === '/recipes') content = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_pages_Recipes__WEBPACK_IMPORTED_MODULE_11__["default"], null);else if (path === '/meal-plans') content = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_pages_MealPlans__WEBPACK_IMPORTED_MODULE_12__["default"], null);else if (path === '/forms') content = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_pages_Forms__WEBPACK_IMPORTED_MODULE_13__["default"], null);else if (path === '/documents') content = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_pages_Documents__WEBPACK_IMPORTED_MODULE_14__["default"], null);else if (path === '/health-tracking') content = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_pages_HealthTracking__WEBPACK_IMPORTED_MODULE_15__["default"], null);else if (path === '/settings') content = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_pages_Settings__WEBPACK_IMPORTED_MODULE_8__["default"], null);else content = (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "Page not found");
    return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_Layout__WEBPACK_IMPORTED_MODULE_3__["default"], null, content);
  }));
};
const root = document.getElementById('practicerx-root');
if (root) {
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.render)((0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(App, null), root);
}
})();

/******/ })()
;
//# sourceMappingURL=index.js.map