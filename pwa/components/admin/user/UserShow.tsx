import { Show, SimpleShowLayout, TextField, DateField, EmailField } from 'react-admin';
import { ProtectedShowActions } from "../PermissionGuards";

export const UserShow = () => {

    return (
        <Show actions={<ProtectedShowActions />}>
            <SimpleShowLayout>
                <TextField source="firstName" label="Prénom" sortable={ true }/>
                <TextField source="lastName" label="Nom" sortable={ true }/>
            </SimpleShowLayout>
        </Show>
    );
}