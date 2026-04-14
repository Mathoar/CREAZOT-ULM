path = "/root/CREAZOT-ULM/pwa/components/admin/client/ClientShow.tsx"
content = open(path).read()

# Wrap Options tab
old = """                <TabbedShowLayout.Tab label="Options">"""
new = """                { isSuperAdminRole && <TabbedShowLayout.Tab label="Options">"""

old_close = """                </TabbedShowLayout.Tab>  
                <TabbedShowLayout.Tab label="Dashboard">"""
new_close = """                </TabbedShowLayout.Tab> }
                <TabbedShowLayout.Tab label="Dashboard">"""

if old in content and "isSuperAdminRole && <TabbedShowLayout.Tab label=\"Options\">" not in content:
    content = content.replace(old, new, 1)
    content = content.replace(old_close, new_close, 1)
    print("Show: Options tab hidden OK")
else:
    print("Show: Options tab already wrapped or pattern not found")

open(path, "w").write(content)
print("Done!")
