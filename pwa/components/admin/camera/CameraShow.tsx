import { Show, SimpleShowLayout, TextField } from 'react-admin';
import { ProtectedShowActions } from "../PermissionGuards";

export const CameraShow = () => (
    <Show actions={<ProtectedShowActions />}>
        <SimpleShowLayout>
            <TextField source="code" label="Code de la caméra" sortable={ true }/>
            <TextField source="nom" label="Nom" sortable={ true }/>
        </SimpleShowLayout>
    </Show>
)