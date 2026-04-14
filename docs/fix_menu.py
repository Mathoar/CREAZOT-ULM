import sys

path = "/root/CREAZOT-ULM/pwa/components/admin/layout/Menu.tsx"
content = open(path).read()

# 1. Options: change isSuperAdmin to isAdmin
old = "{ isSuperAdmin && isDefined(client) && isDefined(client.hasOptions) && client.hasOptions &&"
new = "{ isAdmin && isDefined(client) && isDefined(client.hasOptions) && client.hasOptions &&"
if old in content:
    content = content.replace(old, new)
    print("1. Options -> isAdmin OK")
else:
    print("1. Options pattern not found")

# 2. Remove Contacts from Parametres
old2 = '''                    {/* @ts-ignore */}
                    { isDefined(client) && isDefined(client.hasOriginContact) && client.hasOriginContact &&
                      <Menu.Item
                        to="/contacts"
                        primaryText="Contacts"
                        leftIcon={<PermPhoneMsgIcon />}
                        sx={{ pl: 2, backgroundColor: '#E4E7EB' }}
                      />
                    }'''
if old2 in content:
    content = content.replace(old2, "")
    print("2. Removed Contacts from Parametres OK")
else:
    print("2. Contacts in Parametres pattern not found")

# 3. Add Canaux menu after Aeroports
old3 = '''            { isAdmin &&
              <Menu.Item
                to="/airports"
                primaryText="Aéroports"
                leftIcon={<ConnectingAirportsIcon />}
                sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
              />
            }'''
new3 = old3 + '''
            { isAdmin && isDefined(client) && client.hasOriginContact &&
              <Menu.Item
                to="/client-channels"
                primaryText="Canaux"
                leftIcon={<PermPhoneMsgIcon />}
                sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
              />
            }'''
if old3 in content:
    content = content.replace(old3, new3)
    print("3. Added Canaux menu item OK")
else:
    print("3. Aeroports pattern not found")

# 4. Add Canaux (definition) in Parametres after Natures
old4 = '''                    <Menu.Item
                      to="/natures"
                      primaryText="Natures"
                      leftIcon={<CommentIcon />}
                      sx={{ pl: 2, backgroundColor: '#E4E7EB' }}
                    />'''
new4 = old4 + '''
                    <Menu.Item
                      to="/contacts"
                      primaryText="Canaux (définition)"
                      leftIcon={<PermPhoneMsgIcon />}
                      sx={{ pl: 2, backgroundColor: '#E4E7EB' }}
                    />'''
if old4 in content:
    content = content.replace(old4, new4)
    print("4. Added Canaux definition in Parametres OK")
else:
    print("4. Natures pattern not found")

open(path, "w").write(content)
print("Done!")
