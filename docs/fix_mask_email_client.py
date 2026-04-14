path = "/root/CREAZOT-ULM/pwa/components/admin/client/ClientsEdit.tsx"
content = open(path).read()

# 1. Add PasswordInput to react-admin imports
if "PasswordInput" not in content:
    content = content.replace(
        "useRecordContext }",
        "useRecordContext, PasswordInput }"
    )
    print("1. PasswordInput import added OK")
else:
    print("1. PasswordInput already imported")

# 2. Replace emailParams TextInput with PasswordInput
old_field = '<TextInput source="emailParams" label="Serveur d\'email"/>'
new_field = '<PasswordInput source="emailParams" label="Serveur d\'email"/>'
if old_field in content:
    content = content.replace(old_field, new_field)
    print("2. ClientsEdit emailParams masked OK")
else:
    print("2. Pattern not found, trying alternative...")
    old_alt = 'source="emailParams"'
    if old_alt in content:
        # Find the line and replace TextInput with PasswordInput
        lines = content.split('\n')
        for i, line in enumerate(lines):
            if 'source="emailParams"' in line and 'TextInput' in line:
                lines[i] = line.replace('TextInput', 'PasswordInput')
                print(f"2. Line {i+1} fixed: {lines[i].strip()}")
                break
        content = '\n'.join(lines)
    else:
        print("2. emailParams not found at all")

open(path, "w").write(content)
print("Done!")
