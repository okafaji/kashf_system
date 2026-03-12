# Permission Matrix Report

Generated at: 2026-03-11 10:58:09

## Roles And Permissions

- **admin**: access-admin-dashboard, access-dashboard, create-employees, create-payrolls, delete-employees, delete-payrolls, edit-employees, edit-payrolls, export-payrolls, import-employees, manage-backups, manage-cities, manage-departments, manage-governorates, manage-mission-types, manage-roles, manage-settings, manage-signatures, manage-users, print-payrolls, view-employees, view-payrolls
- **data-entry**: access-dashboard, create-payrolls, view-employees, view-payrolls
- **employee-manager**: access-dashboard, create-employees, delete-employees, edit-employees, import-employees, manage-cities, manage-departments, manage-governorates, manage-users, view-employees
- **payroll-manager**: access-dashboard, create-payrolls, delete-payrolls, edit-payrolls, export-payrolls, manage-mission-types, manage-signatures, manage-users, print-payrolls, view-employees, view-payrolls
- **viewer**: access-dashboard, print-payrolls, view-employees, view-payrolls
- **رئيس قسم**: access-dashboard, print-payrolls, view-employees, view-payrolls
- **مسؤول شعبة**: access-dashboard, view-employees, view-payrolls
- **مسؤول وحدة**: access-dashboard, view-employees, view-payrolls

## Critical Routes Matrix

| Role | dashboard | payrolls.create | payrolls.index | employees.index | signatures.index | departments.index | governorates.index | settings.mission-types | backups.index | users.index | roles.index | admin.dashboard |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| admin | YES | YES | YES | YES | YES | YES | YES | YES | YES | YES | YES | YES |
| data-entry | YES | YES | YES | YES | NO | NO | NO | NO | NO | NO | NO | NO |
| employee-manager | YES | NO | NO | YES | NO | YES | YES | NO | NO | YES | NO | NO |
| payroll-manager | YES | YES | YES | YES | YES | NO | NO | YES | NO | YES | NO | NO |
| viewer | YES | NO | YES | YES | NO | NO | NO | NO | NO | NO | NO | NO |
| رئيس قسم | YES | NO | YES | YES | NO | NO | NO | NO | NO | NO | NO | NO |
| مسؤول شعبة | YES | NO | YES | YES | NO | NO | NO | NO | NO | NO | NO | NO |
| مسؤول وحدة | YES | NO | YES | YES | NO | NO | NO | NO | NO | NO | NO | NO |

## Coverage Summary

Total protected routes (auth + permission): **87**

### admin
- Allowed routes: **87**
- Denied routes: **0**

### data-entry
- Allowed routes: **27**
- Denied routes: **60**
- Sample denied routes: employees.sync (`employees/sync`), payrolls.edit (`payrolls/{id}/edit`), payrolls.update (`payrolls/{id}`), payrolls.add_employee (`payrolls/{kashf_no}/add-employee`), payrolls.destroy (`payrolls/{id}`), payrolls.print_multiple (`payrolls/print-multiple`), payrolls.print (`payrolls/{id}/print`), signatures.edit_all (`settings/signatures`), signatures.update_all (`settings/signatures`), signatures.index (`signatures`), signatures.create (`signatures/create`), signatures.store (`signatures`)

### employee-manager
- Allowed routes: **39**
- Denied routes: **48**
- Sample denied routes: payrolls.mission_rate (`api/mission-rate`), payrolls.index (`payrolls`), payrolls.show (`payrolls/view/{kashf_no}`), payrolls.name_suggest (`payrolls/name-suggest`), payrolls.stats (`payrolls/stats`), payrolls.archive (`payrolls/archive`), payrolls.create (`payrolls/create`), payrolls.store_multiple (`payrolls/store-multiple`), payrolls.store (`payrolls`), - (`payrolls/import-preview`), - (`api/check-duplicates`), payrolls.download-template (`payrolls/download-template`)

### payroll-manager
- Allowed routes: **58**
- Denied routes: **29**
- Sample denied routes: employees.sync (`employees/sync`), admin.dashboard (`admin`), roles.index (`roles`), roles.edit (`roles/{role}/edit`), roles.update (`roles/{role}`), backups.index (`backups`), backups.create (`backups/create`), backups.database (`backups/database`), backups.code (`backups/code`), backups.list (`backups/list`), backups.download (`backups/download/{timestamp}`), backups.open-folder (`backups/open-folder/{timestamp}`)

### viewer
- Allowed routes: **22**
- Denied routes: **65**
- Sample denied routes: payrolls.mission_rate (`api/mission-rate`), employees.sync (`employees/sync`), payrolls.create (`payrolls/create`), payrolls.store_multiple (`payrolls/store-multiple`), payrolls.store (`payrolls`), - (`payrolls/import-preview`), - (`api/check-duplicates`), payrolls.download-template (`payrolls/download-template`), payrolls.edit (`payrolls/{id}/edit`), payrolls.update (`payrolls/{id}`), payrolls.add_employee (`payrolls/{kashf_no}/add-employee`), payrolls.destroy (`payrolls/{id}`)

### رئيس قسم
- Allowed routes: **22**
- Denied routes: **65**
- Sample denied routes: payrolls.mission_rate (`api/mission-rate`), employees.sync (`employees/sync`), payrolls.create (`payrolls/create`), payrolls.store_multiple (`payrolls/store-multiple`), payrolls.store (`payrolls`), - (`payrolls/import-preview`), - (`api/check-duplicates`), payrolls.download-template (`payrolls/download-template`), payrolls.edit (`payrolls/{id}/edit`), payrolls.update (`payrolls/{id}`), payrolls.add_employee (`payrolls/{kashf_no}/add-employee`), payrolls.destroy (`payrolls/{id}`)

### مسؤول شعبة
- Allowed routes: **20**
- Denied routes: **67**
- Sample denied routes: payrolls.mission_rate (`api/mission-rate`), employees.sync (`employees/sync`), payrolls.create (`payrolls/create`), payrolls.store_multiple (`payrolls/store-multiple`), payrolls.store (`payrolls`), - (`payrolls/import-preview`), - (`api/check-duplicates`), payrolls.download-template (`payrolls/download-template`), payrolls.edit (`payrolls/{id}/edit`), payrolls.update (`payrolls/{id}`), payrolls.add_employee (`payrolls/{kashf_no}/add-employee`), payrolls.destroy (`payrolls/{id}`)

### مسؤول وحدة
- Allowed routes: **20**
- Denied routes: **67**
- Sample denied routes: payrolls.mission_rate (`api/mission-rate`), employees.sync (`employees/sync`), payrolls.create (`payrolls/create`), payrolls.store_multiple (`payrolls/store-multiple`), payrolls.store (`payrolls`), - (`payrolls/import-preview`), - (`api/check-duplicates`), payrolls.download-template (`payrolls/download-template`), payrolls.edit (`payrolls/{id}/edit`), payrolls.update (`payrolls/{id}`), payrolls.add_employee (`payrolls/{kashf_no}/add-employee`), payrolls.destroy (`payrolls/{id}`)
