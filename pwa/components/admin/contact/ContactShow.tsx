import { Show, SimpleShowLayout, TextField, NumberField } from 'react-admin';
import { ProtectedShowActions } from "../PermissionGuards";

export const ContactShow = () => (
    <Show actions={<ProtectedShowActions />}>
        <SimpleShowLayout>
            <TextField source="name" label="Nom"/>
        </SimpleShowLayout>
    </Show>
)