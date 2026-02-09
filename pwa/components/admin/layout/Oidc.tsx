import { ForwardedRef, forwardRef } from "react";
import { LogoutClasses } from "react-admin";
import ManageAccountsIcon from '@mui/icons-material/ManageAccounts';
import { ListItemIcon, ListItemText, MenuItem } from "@mui/material";
import { useClient } from '../../admin/ClientProvider';

const Oidc = forwardRef((props, ref: ForwardedRef<any>) => {

    const { client } = useClient();
    const url = client?.url ?? 'https://localhost';
    const authUrl = url !== 'https://localhost' ? `${url}/oidc/` : 'http://localhost:8080';

    return (
        <MenuItem
        className="logout"
        component="li"
        {...props}
        >
             <a
                href={ authUrl }
                target="_blank"
                rel="noopener noreferrer"
                className="flex items-center w-full no-underline text-inherit"
            >      
                <ListItemIcon className={LogoutClasses.icon}>
                        <ManageAccountsIcon fontSize="small" />
                </ListItemIcon>
                <ListItemText>
                    Administration
                </ListItemText>
            </a>
        </MenuItem>
    );
    });
    Oidc.displayName = "Administration";

export default Oidc;
