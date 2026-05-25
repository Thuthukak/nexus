export default {
    module:  'HR',
    label:   'HR',
    order:   20,
    icon:    'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0',
    items: [
        { label: 'Overview',    href: '/hr/dashboard',    permission: null },
        { label: 'Employees',   href: '/hr/employees',    permission: 'hr.employees.view' },
        { label: 'Leave',       href: '/hr/leave',        permission: 'hr.leave.view' },
        { label: 'Departments', href: '/hr/departments',  permission: 'hr.employees.manage' },
    ],
}
