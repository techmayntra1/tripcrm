@extends('layouts.app')
@section('title', 'Meetings')
@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="bi bi-calendar-event-fill icon-gradient bg-tempting-azure"></i>
            </div>
            <div>
                Meetings
                
            </div>
        </div>
        <div class="page-title-actions">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMeetingModal">
                <i class="bi bi-plus-lg me-1"></i> Schedule Meeting
            </button>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="main-card mb-3 card">
            <div class="card-header no-mb">
                <ul class="nav nav-tabs card-header-tabs" id="meetingTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button">
                            <i class="bi bi-clock me-1"></i> Pending
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button">
                            <i class="bi bi-check-circle me-1"></i> Completed
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="rescheduled-tab" data-bs-toggle="tab" data-bs-target="#rescheduled" type="button">
                            <i class="bi bi-arrow-repeat me-1"></i> Rescheduled
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" type="button">
                            <i class="bi bi-calendar-x me-1"></i> Past/Missed
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="deleted-tab" data-bs-toggle="tab" data-bs-target="#deleted" type="button">
                            <i class="bi bi-archive me-1"></i> Deleted
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="meetingTabsContent">
                    <div class="tab-pane fade show active" id="pending" role="tabpanel">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Date & Time</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td><strong>Rajesh Kumar</strong></td>
                                        <td><span class="badge bg-primary">Lead</span></td>
                                        <td>
                                            <i class="bi bi-calendar3 me-1 text-muted"></i> 5 Mar 2026
                                            <br><small class="text-muted"><i class="bi bi-clock me-1"></i> 10:30 AM</small>
                                        </td>
                                        <td>Initial Discussion - Kitchen Design</td>
                                        <td><span class="badge bg-warning">Pending</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-success" title="Mark Complete" data-bs-toggle="modal" data-bs-target="#completeModal">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" title="Reschedule" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                                                <i class="bi bi-calendar-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary" title="Edit" data-bs-toggle="modal" data-bs-target="#editMeetingModal">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="table-light">
                                        <td colspan="7" class="py-2 ps-5">
                                            <span class="font-09"><em>No outcome yet</em></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td><strong>Priya Sharma</strong></td>
                                        <td><span class="badge bg-success">Customer</span></td>
                                        <td>
                                            <i class="bi bi-calendar3 me-1 text-muted"></i> 6 Mar 2026
                                            <br><small class="text-muted"><i class="bi bi-clock me-1"></i> 2:00 PM</small>
                                        </td>
                                        <td>Site Visit - Wardrobe Measurement</td>
                                        <td><span class="badge bg-warning">Pending</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-success" title="Mark Complete" data-bs-toggle="modal" data-bs-target="#completeModal">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" title="Reschedule" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                                                <i class="bi bi-calendar-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary" title="Edit" data-bs-toggle="modal" data-bs-target="#editMeetingModal">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="table-light">
                                        <td colspan="7" class="py-2 ps-5">
                                            <span class="font-09"><em>No outcome yet</em></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td><strong>Amit Patel</strong></td>
                                        <td><span class="badge bg-primary">Lead</span></td>
                                        <td>
                                            <i class="bi bi-calendar3 me-1 text-muted"></i> 7 Mar 2026
                                            <br><small class="text-muted"><i class="bi bi-clock me-1"></i> 11:00 AM</small>
                                        </td>
                                        <td>Budget Discussion - Full Home Interior</td>
                                        <td><span class="badge bg-warning">Pending</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-success" title="Mark Complete" data-bs-toggle="modal" data-bs-target="#completeModal">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" title="Reschedule" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                                                <i class="bi bi-calendar-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary" title="Edit" data-bs-toggle="modal" data-bs-target="#editMeetingModal">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="table-light">
                                        <td colspan="7" class="py-2 ps-5">
                                            <span class="font-09"><em>No outcome yet</em></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                    </div>
                    <div class="tab-pane fade" id="completed" role="tabpanel">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Date & Time</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td><strong>Sunita Verma</strong></td>
                                        <td><span class="badge bg-success">Customer</span></td>
                                        <td>
                                            <i class="bi bi-calendar3 me-1 text-muted"></i> 1 Mar 2026
                                            <br><small class="text-muted"><i class="bi bi-clock me-1"></i> 3:00 PM</small>
                                        </td>
                                        <td>Design Finalization - Living Room</td>
                                        <td><span class="badge bg-success">Completed</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" title="Details">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="table-light">
                                        <td colspan="7" class="py-2 ps-5">
                                            <span class="font-09">Client approved the design. Quotation to be prepared. Follow up on 5th March.</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td><strong>Vikram Singh</strong></td>
                                        <td><span class="badge bg-primary">Lead</span></td>
                                        <td>
                                            <i class="bi bi-calendar3 me-1 text-muted"></i> 28 Feb 2026
                                            <br><small class="text-muted"><i class="bi bi-clock me-1"></i> 11:30 AM</small>
                                        </td>
                                        <td>Initial Consultation - Office Interior</td>
                                        <td><span class="badge bg-success">Completed</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" title="Details">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="table-light">
                                        <td colspan="7" class="py-2 ps-5">
                                            <span class="font-09">Converted to customer. Project value: 5.5 Lakhs. Work starts from 15th March.</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                    </div>
                    <div class="tab-pane fade" id="rescheduled" role="tabpanel">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>New Date & Time</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td><strong>Neha Gupta</strong></td>
                                        <td><span class="badge bg-primary">Lead</span></td>
                                        <td>
                                            <i class="bi bi-calendar3 me-1 text-muted"></i> 10 Mar 2026
                                            <br><small class="text-muted"><i class="bi bi-clock me-1"></i> 4:00 PM</small>
                                            <br><small class="text-info"><i class="bi bi-arrow-repeat me-1"></i> Was: 3 Mar 2026</small>
                                        </td>
                                        <td>Site Measurement - Bedroom</td>
                                        <td><span class="badge bg-info">Rescheduled</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-success" title="Mark Complete" data-bs-toggle="modal" data-bs-target="#completeModal">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" title="Reschedule Again" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                                                <i class="bi bi-calendar-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary" title="Edit" data-bs-toggle="modal" data-bs-target="#editMeetingModal">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="table-light">
                                        <td colspan="7" class="py-2 ps-5">
                                            <span class="font-09">Client requested postponement due to travel. Rescheduled to 10th March.</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                    </div>
                    <div class="tab-pane fade" id="past" role="tabpanel">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Date & Time</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td><strong>Rakesh Jain</strong></td>
                                        <td><span class="badge bg-primary">Lead</span></td>
                                        <td>
                                            <i class="bi bi-calendar3 me-1 text-muted"></i> 25 Feb 2026
                                            <br><small class="text-muted"><i class="bi bi-clock me-1"></i> 10:00 AM</small>
                                        </td>
                                        <td>Initial Discussion - Kitchen</td>
                                        <td><span class="badge bg-secondary">Missed</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info" title="Reschedule" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                                                <i class="bi bi-calendar-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-archive"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="table-light">
                                        <td colspan="7" class="py-2 ps-5">
                                            <span class="font-09">Client did not show up. No response on calls.</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td><strong>Meera Shah</strong></td>
                                        <td><span class="badge bg-success">Customer</span></td>
                                        <td>
                                            <i class="bi bi-calendar3 me-1 text-muted"></i> 20 Feb 2026
                                            <br><small class="text-muted"><i class="bi bi-clock me-1"></i> 5:00 PM</small>
                                        </td>
                                        <td>Final Payment Discussion</td>
                                        <td><span class="badge bg-dark">Cancelled</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info" title="Reschedule" data-bs-toggle="modal" data-bs-target="#rescheduleModal">
                                                <i class="bi bi-calendar-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="bi bi-archive"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="table-light">
                                        <td colspan="7" class="py-2 ps-5">
                                            <span class="font-09">Meeting cancelled by client. Will schedule next week.</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                    </div>
                    <div class="tab-pane fade" id="deleted" role="tabpanel">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Date & Time</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="bi bi-archive fs-3 d-block mb-2"></i>
                                            No deleted meetings.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('modals')
<div class="modal fade" id="addMeetingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header meeting-header">
                <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Schedule Meeting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="meeting_type" class="form-label">Meeting With <span class="text-danger">*</span></label>
                            <select class="form-select" id="meeting_type" required>
                                <option value="">Select Type</option>
                                <option value="lead">Lead</option>
                                <option value="customer">Customer</option>
                                <option value="vendor">Vendor</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_name" class="form-label">Select Name <span class="text-danger">*</span></label>
                            <select class="form-select" id="contact_name" required>
                                <option value="">First select meeting with</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="meeting_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" placeholder="dd/mm/yyyy" id="meeting_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="meeting_time" class="form-label">Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="meeting_time" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="meeting_purpose" class="form-label">Purpose <span class="text-danger">*</span></label>
                        <select class="form-select" id="meeting_purpose" required>
                            <option value="">Select Purpose</option>
                            <option value="1">Initial Discussion</option>
                            <option value="2">Site Visit</option>
                            <option value="3">Design Presentation</option>
                            <option value="4">Budget Discussion</option>
                            <option value="5">Design Finalization</option>
                            <option value="6">Material Selection</option>
                            <option value="7">Progress Review</option>
                            <option value="8">Payment Discussion</option>
                            <option value="9">Handover</option>
                            <option value="10">Follow Up</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="meeting_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="meeting_notes" rows="2" placeholder="Any additional notes..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary"><i class="bi bi-calendar-check me-1"></i> Schedule</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editMeetingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Meeting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_meeting_type" class="form-label">Meeting With <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_meeting_type" required>
                                <option value="lead" selected>Lead</option>
                                <option value="customer">Customer</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_contact_name" class="form-label">Select Name <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_contact_name" required>
                                <option value="1" selected>Rajesh Kumar</option>
                                <option value="2">Amit Patel</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_meeting_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" placeholder="dd/mm/yyyy" id="edit_meeting_date" value="2026-03-05" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_meeting_time" class="form-label">Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="edit_meeting_time" value="10:30" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_meeting_purpose" class="form-label">Purpose <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_meeting_purpose" required>
                            <option value="">Select Purpose</option>
                            <option value="1" selected>Initial Discussion</option>
                            <option value="2">Site Visit</option>
                            <option value="3">Design Presentation</option>
                            <option value="4">Budget Discussion</option>
                            <option value="5">Design Finalization</option>
                            <option value="6">Material Selection</option>
                            <option value="7">Progress Review</option>
                            <option value="8">Payment Discussion</option>
                            <option value="9">Handover</option>
                            <option value="10">Follow Up</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status">
                            <option value="pending" selected>Pending</option>
                            <option value="completed">Completed</option>
                            <option value="rescheduled">Rescheduled</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_meeting_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="edit_meeting_notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary me-auto"><i class="bi bi-archive me-1"></i> Delete</button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle me-2"></i>Complete Meeting</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Meeting Details</label>
                        <div class="p-3 bg-light rounded">
                            <strong>Rajesh Kumar</strong> (Lead)<br>
                            <small class="text-muted">5 Mar 2026 at 10:30 AM</small><br>
                            <small>Initial Discussion - Kitchen Design</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="outcome" class="form-label">Meeting Outcome <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="outcome" rows="3" placeholder="Describe the outcome of this meeting..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="next_action" class="form-label">Next Action</label>
                        <input type="text" class="form-control" id="next_action" placeholder="e.g., Send quotation, Schedule site visit">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> Mark Complete</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Reschedule Meeting</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Current Schedule</label>
                        <div class="p-3 bg-light rounded">
                            <strong>Rajesh Kumar</strong> (Lead)<br>
                            <small class="text-muted">5 Mar 2026 at 10:30 AM</small><br>
                            <small>Initial Discussion - Kitchen Design</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_date" class="form-label">New Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" placeholder="dd/mm/yyyy" id="new_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_time" class="form-label">New Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="new_time" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="reschedule_reason" class="form-label">Reason for Rescheduling</label>
                        <textarea class="form-control" id="reschedule_reason" rows="2" placeholder="Why is this meeting being rescheduled?"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info"><i class="bi bi-calendar-check me-1"></i> Reschedule</button>
            </div>
        </div>
    </div>
</div>
@endpush
@push('scripts')
<script>
    document.getElementById('meeting_type').addEventListener('change', function() {
        const nameSelect = document.getElementById('contact_name');
        nameSelect.innerHTML = '';
        if (this.value === 'lead') {
            nameSelect.innerHTML = `
                <option value="">Select Lead</option>
                <option value="1">Rajesh Kumar</option>
                <option value="2">Amit Patel</option>
                <option value="3">Neha Gupta</option>
                <option value="4">Rakesh Jain</option>
            `;
        } else if (this.value === 'customer') {
            nameSelect.innerHTML = `
                <option value="">Select Customer</option>
                <option value="1">Priya Sharma</option>
                <option value="2">Sunita Verma</option>
                <option value="3">Vikram Singh</option>
                <option value="4">Meera Shah</option>
            `;
        } else {
            nameSelect.innerHTML = '<option value="">First select type</option>';
        }
    });
</script>
@endpush
@endsection
