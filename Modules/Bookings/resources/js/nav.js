export default {
    module:  'Bookings',
    label:   'Bookings',
    order:   30,
    icon:    'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    items: [
        { label: 'Overview',  href: '/bookings/dashboard', permission: null },
        { label: 'Bookings',  href: '/bookings/bookings',  permission: 'bookings.bookings.view' },
        { label: 'Services',  href: '/bookings/services',  permission: 'bookings.services.manage' },
        { label: 'Resources', href: '/bookings/resources', permission: 'bookings.resources.manage' },
    ],
}
