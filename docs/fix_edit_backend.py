path = "/root/CREAZOT-ULM/pwa/components/admin/client/ClientsEdit.tsx"
content = open(path).read()

# 1. Fix fetch headers: add X-Client-Id
old_headers = """                headers: {
                    'Authorization': `Bearer ${session?.accessToken}`,
                    'Content-Type': 'application/json'
                }"""

new_headers = """                headers: {
                    'Authorization': `Bearer ${session?.accessToken}`,
                    'Content-Type': 'application/json',
                    ...(() => { try { const c = JSON.parse(sessionStorage.getItem('client') || '{}'); return c?.id ? { 'X-Client-Id': String(c.id) } : {}; } catch(e) { return {}; } })()
                }"""

if old_headers in content:
    content = content.replace(old_headers, new_headers)
    print("Fix 1: X-Client-Id header added OK")
else:
    print("Fix 1: header pattern not found!")

# 2. Fix redirect: admins stay on edit page, super_admins go to list
old_redirect = """            updateClient(updatedClient);
            notify('Le client a bien été mis à jour.', { type: 'success' });
            redirect('list', 'clients');"""

new_redirect = """            updateClient(updatedClient);
            notify('Le client a bien été mis à jour.', { type: 'success' });
            if (isSuperAdminRole) {
                redirect('list', 'clients');
            }"""

if old_redirect in content:
    content = content.replace(old_redirect, new_redirect)
    print("Fix 2: conditional redirect OK")
else:
    print("Fix 2: redirect pattern not found!")

open(path, "w").write(content)
print("Done!")
