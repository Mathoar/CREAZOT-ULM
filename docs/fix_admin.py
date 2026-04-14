path = "/root/CREAZOT-ULM/pwa/components/admin/Admin.tsx"
content = open(path).read()

# 1. Add import
import_line = 'import { ClientChannels } from "./channel/ClientChannels";'
if import_line not in content:
    content = content.replace(
        'import { MembersList } from "./members/MembersList";',
        'import { MembersList } from "./members/MembersList";\n' + import_line
    )
    print("1. Import added OK")
else:
    print("1. Import already present")

# 2. Add route
route_line = '          <Route path="/client-channels" element={<ClientChannels />} />'
if '/client-channels' not in content:
    content = content.replace(
        '          <Route path="/members" element={<MembersList />} />',
        '          <Route path="/members" element={<MembersList />} />\n' + route_line
    )
    print("2. Route added OK")
else:
    print("2. Route already present")

open(path, "w").write(content)
print("Done!")
