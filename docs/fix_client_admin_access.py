import re

# =============================================
# 1. MENU — Ajouter "Mon établissement" pour admin, garder "Client" pour super_admin
# =============================================
menu_path = "/root/CREAZOT-ULM/pwa/components/admin/layout/Menu.tsx"
menu = open(menu_path).read()

# Add BusinessIcon import
if "BusinessIcon" not in menu:
    menu = menu.replace(
        "import PersonIcon from '@mui/icons-material/Person';",
        "import PersonIcon from '@mui/icons-material/Person';\nimport BusinessIcon from '@mui/icons-material/Business';"
    )
    print("Menu: BusinessIcon import OK")

# Add admin menu entry before the existing super_admin one
old_client_menu = """            { isSuperAdmin &&
            <Menu.Item
                  to="/clients"
                  primaryText="Client"
                  leftIcon={<PersonIcon />}
                  sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
                />
            }"""

new_client_menu = """            { isAdmin && !isSuperAdmin && isDefined(client) &&
              <Menu.Item
                to={`/clients/${client.id}`}
                primaryText="Mon \u00e9tablissement"
                leftIcon={<BusinessIcon />}
                sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
              />
            }
            { isSuperAdmin &&
              <Menu.Item
                to="/clients"
                primaryText="Clients"
                leftIcon={<PersonIcon />}
                sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
              />
            }"""

if old_client_menu in menu:
    menu = menu.replace(old_client_menu, new_client_menu)
    print("Menu: admin entry added OK")
else:
    print("Menu: client menu pattern not found!")

open(menu_path, "w").write(menu)

# =============================================
# 2. ClientsEdit — Restrict for admins
# =============================================
edit_path = "/root/CREAZOT-ULM/pwa/components/admin/client/ClientsEdit.tsx"
edit = open(edit_path).read()

# 2a. Add useClient import if not present
if "useClient" not in edit:
    edit = edit.replace(
        "import { useSessionContext } from '../SessionContextProvider';",
        "import { useSessionContext } from '../SessionContextProvider';\nimport { useClient } from '../ClientProvider';"
    )
    print("Edit: useClient import OK")
else:
    print("Edit: useClient already imported")

# 2b. Add isSuperAdmin from useClient at the top of the component
# Find the component function and add the hook
if "const { isSuperAdmin: isSuperAdminRole }" not in edit:
    # Find "const { session }" line and add after it
    edit = edit.replace(
        "const { session } = useSessionContext();",
        "const { session } = useSessionContext();\n    const { isSuperAdmin: isSuperAdminRole } = useClient();"
    )
    print("Edit: isSuperAdmin hook added OK")

# 2c. Hide "active" checkbox for non-super-admins
old_active = '<BooleanInput source="active" label="Utilisateur actif" />'
new_active = '{ isSuperAdminRole && <BooleanInput source="active" label="Utilisateur actif" /> }'
if old_active in edit:
    edit = edit.replace(old_active, new_active)
    print("Edit: active hidden for non-super OK")

# 2d. Hide Options tab for non-super-admins
old_options_tab = '                    <TabbedForm.Tab label="Options">'
new_options_tab = '                    { isSuperAdminRole && <TabbedForm.Tab label="Options">'
# Find the closing of Options tab
old_options_close = """                    </TabbedForm.Tab>
                    <TabbedForm.Tab label="Dashboard">"""
new_options_close = """                    </TabbedForm.Tab> }
                    <TabbedForm.Tab label="Dashboard">"""

if old_options_tab in edit and '{ isSuperAdminRole && <TabbedForm.Tab label="Options">' not in edit:
    edit = edit.replace(old_options_tab, new_options_tab, 1)
    edit = edit.replace(old_options_close, new_options_close, 1)
    print("Edit: Options tab hidden for non-super OK")

# 2e. Hide Abonnement tab for non-super-admins
old_abo_tab = '                    <TabbedForm.Tab label="Abonnement">'
new_abo_tab = '                    { isSuperAdminRole && <TabbedForm.Tab label="Abonnement">'

# Find closing of Abonnement tab (it's the last tab before </TabbedForm>)
old_abo_close = """                    </TabbedForm.Tab>
                </TabbedForm>"""
new_abo_close = """                    </TabbedForm.Tab> }
                </TabbedForm>"""

if old_abo_tab in edit and '{ isSuperAdminRole && <TabbedForm.Tab label="Abonnement">' not in edit:
    edit = edit.replace(old_abo_tab, new_abo_tab, 1)
    edit = edit.replace(old_abo_close, new_abo_close, 1)
    print("Edit: Abonnement tab hidden for non-super OK")

open(edit_path, "w").write(edit)

# =============================================
# 3. ThanksOptions — hasEmailConfirmation disabled if no email config
# =============================================
thanks_path = "/root/CREAZOT-ULM/pwa/components/admin/client/ThanksOptions.jsx"
thanks = open(thanks_path).read()

# Add watch for emailParams and emailAddressSender
if "emailParams" not in thanks:
    thanks = thanks.replace(
        "  const hasEmailConfirmation = useWatch({ name: 'hasEmailConfirmation', defaultValue: false });",
        "  const hasEmailConfirmation = useWatch({ name: 'hasEmailConfirmation', defaultValue: false });\n  const emailParams = useWatch({ name: 'emailParams', defaultValue: '' });\n  const emailAddressSender = useWatch({ name: 'emailAddressSender', defaultValue: '' });\n  const emailConfigMissing = !emailParams || !emailAddressSender;"
    )
    print("Thanks: email watch added OK")

    # Add disabled + helperText to BooleanInput
    old_bool = '<BooleanInput source="hasEmailConfirmation" label="Email de confirmation" sx={{marginTop: \'1em\'}} fullWidth/>'
    new_bool = '<BooleanInput source="hasEmailConfirmation" label="Email de confirmation" sx={{marginTop: \'1em\'}} fullWidth disabled={emailConfigMissing} helperText={emailConfigMissing ? "Configurez le serveur d\'email et l\'adresse d\'envoi dans l\'onglet Informations" : ""}/>'
    if old_bool in thanks:
        thanks = thanks.replace(old_bool, new_bool)
        print("Thanks: BooleanInput disabled OK")
    else:
        print("Thanks: BooleanInput pattern not found")
else:
    print("Thanks: emailParams already present")

open(thanks_path, "w").write(thanks)

# =============================================
# 4. ClientShow — Restrict for admins
# =============================================
show_path = "/root/CREAZOT-ULM/pwa/components/admin/client/ClientShow.tsx"
show = open(show_path).read()

# 4a. Add useClient import
if "useClient" not in show:
    show = show.replace(
        "import { isDefined } from '../../../app/lib/utils';",
        "import { isDefined } from '../../../app/lib/utils';\nimport { useClient } from '../ClientProvider';"
    )
    print("Show: useClient import OK")

# 4b. Add hook
if "isSuperAdmin" not in show:
    show = show.replace(
        "const getDescription = ({ address, zipcode, city }) => {",
        "const { isSuperAdmin: isSuperAdminRole } = useClient();\n\n    const getDescription = ({ address, zipcode, city }) => {"
    )
    print("Show: isSuperAdmin hook added OK")

# 4c. Hide "active" field for non-super
old_show_active = '                    <BooleanField source="active" label="Compte activé" textAlign="center"/>'
new_show_active = '                    { isSuperAdminRole && <BooleanField source="active" label="Compte activé" textAlign="center"/> }'
if old_show_active in show:
    show = show.replace(old_show_active, new_show_active)
    print("Show: active hidden OK")

# 4d. Hide Options tab
old_show_options = '                <TabbedShowLayout.Tab label="Options">'
new_show_options = '                { isSuperAdminRole && <TabbedShowLayout.Tab label="Options">'
old_show_options_close = """                </TabbedShowLayout.Tab>  
                <TabbedShowLayout.Tab label="Dashboard">"""
new_show_options_close = """                </TabbedShowLayout.Tab> }
                <TabbedShowLayout.Tab label="Dashboard">"""

if old_show_options in show and '{ isSuperAdminRole &&' not in show.split('Options')[0]:
    show = show.replace(old_show_options, new_show_options, 1)
    show = show.replace(old_show_options_close, new_show_options_close, 1)
    print("Show: Options tab hidden OK")

# 4e. Also mask emailParams in Show for non-super
old_show_email = '                    <TextField source="emailParams" label="Serveur d\'email SendGrid"/>'
new_show_email = '                    { isSuperAdminRole ? <TextField source="emailParams" label="Serveur d\'email"/> : <TextField source="emailAddressSender" label="Email d\'envoi configuré"/> }'
if old_show_email in show:
    show = show.replace(old_show_email, new_show_email)
    print("Show: emailParams masked OK")

open(show_path, "w").write(show)

print("\n=== All done! ===")
