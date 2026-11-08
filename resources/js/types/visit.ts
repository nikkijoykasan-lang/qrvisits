export type VisitStatus =
    | 'pending'
    | 'approved'
    | 'denied'
    | 'inside'
    | 'completed';

export type Visit = {
    id: string;
    visitor_name: string;
    visitor_contact: string;
    visitor_email: string | null;
    purpose: string;
    host_name: string;
    visit_date: string;
    expected_arrival: string | null;
    expected_time_out: string | null;
    expected_time_out_at: string | null;
    is_overdue: boolean;
    overdue_minutes: number;
    status: VisitStatus;
    status_label: string;
    pass_code: string | null;
    denial_reason: string | null;
    is_walk_in: boolean;
    id_document_name: string | null;
    id_document_url: string | null;
    approved_at: string | null;
    approved_by_name: string | null;
    time_in: string | null;
    time_out: string | null;
    checked_in_by_name: string | null;
    checked_out_by_name: string | null;
    created_at: string;
    can_scan: boolean;
    pass_qr: string | null;
};

export type VisitStats = {
    pending: number;
    inside: number;
    expected_today: number;
    completed_today: number;
    walk_ins_today: number;
    overdue: number;
};

export type OverdueVisitorAlert = {
    id: string;
    visitor_name: string;
    pass_code: string | null;
    expected_time_out: string;
    time_in: string | null;
    minutes_overdue: number;
    visit_url: string;
};

export type OverdueAlertResponse = {
    count: number;
    visits: OverdueVisitorAlert[];
};

export type VisitorStats = {
    pending: number;
    approved: number;
    total: number;
};

export type Pagination = {
    current_page: number;
    last_page: number;
    previous_page_url: string | null;
    next_page_url: string | null;
};

export type StaffAccount = {
    id: string;
    name: string;
    email: string;
    role: 'guard' | 'admin';
    role_label: string;
    created_at: string;
};

export type ScanResponse = {
    success: boolean;
    outcome: string;
    message: string;
    visit: Visit | null;
};
