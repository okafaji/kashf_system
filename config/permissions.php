<?php

return [
    // قائمة الصلاحيات المسموح بها في النظام
    'permissions' => [
        // صلاحيات الوصول
        'access-dashboard',
        'access-admin-dashboard',
        'access-departments-page',
        'access-governorates-page',
        'access-mission-types-page',

        // صلاحيات الكشوفات
        'create-payrolls',
        'view-payrolls',
        'edit-payrolls',
        'delete-payrolls',
        'print-payrolls',
        'export-payrolls',

        // صلاحيات الموظفين
        'create-employees',
        'view-employees',
        'edit-employees',
        'delete-employees',
        'import-employees',

        // صلاحيات التوقيعات
        'manage-signatures',

        // صلاحيات الإعدادات
        'manage-settings',
        'manage-users',
        'manage-roles',
        'manage-backups',
        'manage-mission-types',

        // صلاحيات المراقبة والتدقيق
        'view-payroll-audit-log',
        'view-system-health-page',

        // صلاحيات الأقسام والمحافظات
        'manage-departments',
        'manage-governorates',
        'manage-cities',
    ],

    // تصنيفات الصلاحيات وتسمية كل صلاحية (تُستخدم في واجهة تعديل الأدوار)
    'permission_groups' => [
        'access' => [
            'label' => 'صلاحيات الوصول',
            'color' => 'text-cyan-700',
            'columns' => 1,
            'permissions' => [
                'access-dashboard' => 'الوصول إلى لوحة التحكم والإحصائيات',
                'access-admin-dashboard' => 'الوصول إلى لوحة الأدمن',
            ],
        ],
        'page_access' => [
            'label' => 'إظهار الصفحات',
            'color' => 'text-sky-700',
            'columns' => 1,
            'permissions' => [
                'access-departments-page' => 'إظهار صفحة الأقسام',
                'access-governorates-page' => 'إظهار صفحة المحافظات',
                'access-mission-types-page' => 'إظهار صفحة إيفاد خارج البلد',
            ],
        ],
        'payrolls' => [
            'label' => 'صلاحيات الكشوفات',
            'color' => 'text-blue-600',
            'columns' => 1,
            'permissions' => [
                'create-payrolls' => 'إنشاء كشوفات',
                'view-payrolls' => 'عرض الكشوفات',
                'edit-payrolls' => 'تعديل الكشوفات',
                'delete-payrolls' => 'حذف الكشوفات',
                'print-payrolls' => 'طباعة الكشوفات',
                'export-payrolls' => 'تصدير الكشوفات',
            ],
        ],
        'employees' => [
            'label' => 'صلاحيات الموظفين',
            'color' => 'text-green-600',
            'columns' => 1,
            'permissions' => [
                'create-employees' => 'إضافة موظفين',
                'view-employees' => 'عرض الموظفين',
                'edit-employees' => 'تعديل الموظفين',
                'delete-employees' => 'حذف الموظفين',
                'import-employees' => 'استيراد موظفين',
            ],
        ],
        'signatures' => [
            'label' => 'صلاحيات التواقيع',
            'color' => 'text-purple-600',
            'columns' => 1,
            'permissions' => [
                'manage-signatures' => 'إدارة التواقيع',
            ],
        ],
        'settings' => [
            'label' => 'صلاحيات النظام والإعدادات',
            'color' => 'text-red-600',
            'columns' => 1,
            'permissions' => [
                'manage-settings' => 'إدارة الإعدادات العامة',
                'manage-users' => 'إدارة المستخدمين',
                'manage-roles' => 'إدارة الأدوار',
                'manage-backups' => 'إدارة النسخ الاحتياطية',
                'manage-mission-types' => 'إدارة أنواع الإيفاد',
            ],
        ],
        'monitoring' => [
            'label' => 'صلاحيات المراقبة',
            'color' => 'text-violet-700',
            'columns' => 1,
            'permissions' => [
                'view-payroll-audit-log' => 'عرض سجل تدقيق الكشوفات',
                'view-system-health-page' => 'عرض صفحة صحة النظام',
            ],
        ],
        'locations' => [
            'label' => 'صلاحيات الأقسام والمحافظات والمدن',
            'color' => 'text-orange-600',
            'columns' => 3,
            'permissions' => [
                'manage-departments' => 'إدارة الأقسام',
                'manage-governorates' => 'إدارة المحافظات',
                'manage-cities' => 'إدارة المدن',
            ],
        ],
    ],

    // شرح تفصيلي لتأثير كل صلاحية عند التفعيل أو الإلغاء
    'permission_help' => [
        'access-dashboard' => [
            'enable' => 'يسمح بدخول لوحة التحكم ومشاهدة الإحصائيات العامة.',
            'disable' => 'يمنع الوصول إلى لوحة التحكم الرئيسية وودجات الإحصائيات.',
        ],
        'access-admin-dashboard' => [
            'enable' => 'يسمح بدخول لوحة الأدمن المخصصة للمتابعة الإدارية.',
            'disable' => 'يمنع فتح لوحة الأدمن حتى لو كان المستخدم يملك صلاحيات أخرى.',
        ],
        'access-departments-page' => [
            'enable' => 'يُظهر صفحة الأقسام وروابطها في القوائم.',
            'disable' => 'يخفي صفحة الأقسام من القوائم ويمنع فتحها مباشرة.',
        ],
        'access-governorates-page' => [
            'enable' => 'يُظهر صفحة المحافظات/المدن وروابطها.',
            'disable' => 'يخفي صفحة المحافظات/المدن ويمنع الوصول لها.',
        ],
        'access-mission-types-page' => [
            'enable' => 'يُظهر صفحة ايفاد خارج البلد (أنواع الإيفاد) في القوائم.',
            'disable' => 'يخفي صفحة أنواع الإيفاد ويمنع فتحها.',
        ],

        'create-payrolls' => [
            'enable' => 'يسمح بإنشاء كشوفات إيفاد جديدة.',
            'disable' => 'يمنع إضافة أي كشف جديد.',
        ],
        'view-payrolls' => [
            'enable' => 'يسمح بعرض سجل الكشوفات وتفاصيلها.',
            'disable' => 'يمنع فتح سجل الكشوفات أو مشاهدة تفاصيلها.',
        ],
        'edit-payrolls' => [
            'enable' => 'يسمح بتعديل بيانات الكشوفات الموجودة.',
            'disable' => 'يمنع أي تعديل على الكشوفات بعد إنشائها.',
        ],
        'delete-payrolls' => [
            'enable' => 'يسمح بحذف الكشوفات من النظام.',
            'disable' => 'يمنع حذف الكشوفات ويجعلها للقراءة/التعديل فقط بحسب باقي الصلاحيات.',
        ],
        'print-payrolls' => [
            'enable' => 'يسمح بطباعة كشف واحد أو عدة كشوفات.',
            'disable' => 'يمنع أوامر الطباعة من الواجهة.',
        ],
        'export-payrolls' => [
            'enable' => 'يسمح بتصدير بيانات الكشوفات (مثل Excel/PDF حسب النظام).',
            'disable' => 'يمنع تصدير البيانات خارج النظام.',
        ],

        'create-employees' => [
            'enable' => 'يسمح بإضافة منتسبين جدد يدويًا.',
            'disable' => 'يمنع إضافة منتسب جديد.',
        ],
        'view-employees' => [
            'enable' => 'يسمح بعرض قائمة المنتسبين وبياناتهم.',
            'disable' => 'يمنع الوصول إلى شاشة المنتسبين.',
        ],
        'edit-employees' => [
            'enable' => 'يسمح بتعديل بيانات المنتسبين.',
            'disable' => 'يمنع تعديل بيانات المنتسبين.',
        ],
        'delete-employees' => [
            'enable' => 'يسمح بحذف المنتسبين من النظام.',
            'disable' => 'يمنع حذف المنتسبين.',
        ],
        'import-employees' => [
            'enable' => 'يسمح باستيراد المنتسبين من ملفات خارجية.',
            'disable' => 'يمنع الاستيراد الجماعي للمنتسبين.',
        ],

        'manage-signatures' => [
            'enable' => 'يسمح بإدارة التواقيع الرسمية المستخدمة في التقارير.',
            'disable' => 'يمنع تعديل/إضافة/حذف التواقيع.',
        ],

        'manage-settings' => [
            'enable' => 'صلاحية إدارية عامة لإعدادات النظام الحساسة.',
            'disable' => 'يحد من التحكم المركزي في إعدادات النظام.',
        ],
        'manage-users' => [
            'enable' => 'يسمح بإدارة حسابات المستخدمين (إنشاء/تعديل/حذف/تعيين أدوار حسب القيود).',
            'disable' => 'يمنع إدارة المستخدمين. إذا كانت آخر صلاحية وصول إدارية فسيتم تفعيل البديل تلقائيًا.',
        ],
        'manage-roles' => [
            'enable' => 'يسمح بالدخول إلى إدارة الأدوار وتعديل صلاحياتها.',
            'disable' => 'يمنع تعديل الأدوار. إذا كانت آخر صلاحية وصول إدارية فسيتم تفعيل البديل تلقائيًا.',
        ],
        'manage-backups' => [
            'enable' => 'يسمح بإنشاء/تحميل/حذف النسخ الاحتياطية.',
            'disable' => 'يمنع التعامل مع النسخ الاحتياطية.',
        ],
        'manage-mission-types' => [
            'enable' => 'يسمح بإدارة أنواع الإيفاد (إضافة/تعديل/حذف).',
            'disable' => 'يُبقي صفحة الأنواع للعرض فقط إذا كانت صلاحية الإظهار موجودة.',
        ],
        'view-payroll-audit-log' => [
            'enable' => 'يسمح بعرض سجل تدقيق عمليات الكشوفات (من غيّر ماذا ومتى).',
            'disable' => 'يمنع الوصول إلى سجل التدقيق.',
        ],
        'view-system-health-page' => [
            'enable' => 'يسمح بعرض صفحة صحة النظام (النسخ الاحتياطية، حالة قاعدة البيانات، السجلات).',
            'disable' => 'يمنع الوصول إلى صفحة صحة النظام.',
        ],

        'manage-departments' => [
            'enable' => 'يسمح بإدارة الأقسام (إضافة/تعديل/حذف).',
            'disable' => 'يجعل صفحة الأقسام للعرض فقط عند وجود صلاحية إظهار الصفحة.',
        ],
        'manage-governorates' => [
            'enable' => 'يسمح بإدارة المحافظات وبياناتها الأساسية.',
            'disable' => 'يجعل صفحة المحافظات للعرض فقط عند وجود صلاحية الإظهار.',
        ],
        'manage-cities' => [
            'enable' => 'يسمح بإدارة المدن وبدلات الإيفاد التابعة للمحافظات.',
            'disable' => 'يمنع إضافة/تعديل/حذف المدن مع بقاء العرض حسب صلاحية الصفحة.',
        ],
    ],

    // شرح تفصيلي لهدف كل دور
    'role_help' => [
        'admin' => 'صلاحيات كاملة على جميع الوحدات والصفحات والإعدادات.',
        'payroll-manager' => 'يركز على إدارة الكشوفات والطباعة والتصدير مع صلاحيات محددة للإعدادات المرتبطة بها.',
        'employee-manager' => 'يركز على إدارة المنتسبين والأقسام والمحافظات والمدن.',
        'data-entry' => 'إدخال ومراجعة أولية للكشوفات دون صلاحيات إدارية متقدمة.',
        'viewer' => 'عرض وقراءة فقط مع صلاحيات طباعة حسب الإعداد.',
        'رئيس قسم' => 'دور إشرافي للمتابعة والاطلاع على الكشوفات دون إدارة كاملة.',
        'مسؤول شعبة' => 'متابعة تشغيلية للشعبة مع عرض البيانات الأساسية.',
        'مسؤول وحدة' => 'متابعة تشغيلية للوحدة مع صلاحيات عرض محددة.',
    ],

    // صلاحيات تحديد الصفحات (نقطة مركزية لتعديل صلاحيات الشاشات)
    'page_permissions' => [
        'departments' => [
            'access' => [
                'access-departments-page',
            ],
            'manage' => [
                'manage-settings',
                'manage-departments',
            ],
        ],
        'governorates' => [
            'access' => [
                'access-governorates-page',
            ],
            'manage' => [
                'manage-settings',
                'manage-governorates',
            ],
        ],
        'mission_types' => [
            'access' => [
                'access-mission-types-page',
            ],
            'manage' => [
                'manage-settings',
                'manage-mission-types',
            ],
        ],
        'cities' => [
            'access' => [
                'access-governorates-page',
            ],
            'manage' => [
                'manage-settings',
                'manage-governorates',
                'manage-cities',
            ],
        ],
        'audit' => [
            'access' => [
                'view-payroll-audit-log',
            ],
        ],
        'system_health' => [
            'access' => [
                'view-system-health-page',
            ],
        ],
    ],

    // ربط الأدوار بصلاحياتها
    'roles' => [
        'admin' => '*',

        'payroll-manager' => [
            'access-dashboard',
            'access-mission-types-page',
            'create-payrolls',
            'view-payrolls',
            'edit-payrolls',
            'delete-payrolls',
            'print-payrolls',
            'export-payrolls',
            'view-employees',
            'manage-signatures',
            'manage-users',
            'manage-mission-types',
            'view-payroll-audit-log',
        ],

        'data-entry' => [
            'access-dashboard',
            'create-payrolls',
            'view-payrolls',
            'view-employees',
        ],

        'viewer' => [
            'access-dashboard',
            'view-payrolls',
            'print-payrolls',
            'view-employees',
        ],

        'employee-manager' => [
            'access-dashboard',
            'access-departments-page',
            'access-governorates-page',
            'create-employees',
            'view-employees',
            'edit-employees',
            'delete-employees',
            'import-employees',
            'manage-departments',
            'manage-governorates',
            'manage-cities',
            'manage-users',
            'view-payroll-audit-log',
        ],

        'رئيس قسم' => [
            'access-dashboard',
            'view-payrolls',
            'print-payrolls',
            'view-employees',
        ],

        'مسؤول شعبة' => [
            'access-dashboard',
            'view-payrolls',
            'view-employees',
        ],

        'مسؤول وحدة' => [
            'access-dashboard',
            'view-payrolls',
            'view-employees',
        ],
    ],
];
