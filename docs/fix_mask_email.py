path = "/root/CREAZOT-ULM/pwa/components/admin/siteSettings/SiteSettingsEdit.tsx"
content = open(path).read()

# 1. Add PasswordInput to react-admin imports
old_import = 'import { TextInput, SimpleForm, Edit, FileInput, FileField, useRecordContext } from "react-admin";'
new_import = 'import { TextInput, PasswordInput, SimpleForm, Edit, FileInput, FileField, useRecordContext } from "react-admin";'
if "PasswordInput" not in content:
    content = content.replace(old_import, new_import)
    print("1. PasswordInput import added OK")
else:
    print("1. PasswordInput already imported")

# 2. Replace emailParams TextInput with PasswordInput
old_field = '<TextInput source="emailParams" label="Serveur d\'email (paramètres)" fullWidth />'
new_field = '<PasswordInput source="emailParams" label="Serveur d\'email (paramètres)" fullWidth />'
if old_field in content:
    content = content.replace(old_field, new_field)
    print("2. emailParams masked OK")
else:
    print("2. emailParams pattern not found")

open(path, "w").write(content)
print("Done!")
