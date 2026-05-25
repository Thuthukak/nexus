export default {
    module:  'Financial',
    label:   'Financial',
    order:   10,
    icon:    'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
    items: [
        { label: 'Overview',  href: '/financial/dashboard',         permission: null },
        { label: 'Invoices',  href: '/financial/invoices',           permission: 'financial.invoices.view' },
        { label: 'Customers', href: '/financial/customers',          permission: 'financial.customers.manage' },
        { label: 'Tax Rates', href: '/financial/tax-rates',          permission: 'financial.invoices.manage' },
        { label: 'Recurring', href: '/financial/recurring',          permission: 'financial.invoices.manage' },
        { label: 'Payments',  href: '/financial/settings/payments',  permission: 'core.settings.manage' },
    ],
}
