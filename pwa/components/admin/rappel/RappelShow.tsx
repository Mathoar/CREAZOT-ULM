import { Show, SimpleShowLayout, TextField, DateField, BooleanField, FunctionField } from 'react-admin';
import { ProtectedShowActions } from "../PermissionGuards";

export const RappelShow = () => {

    const getRecurrenceLabel = ({recurrent, jour}) => {
    const weekDays = ['Dimanches', 'Lundis', 'Mardis', 'Mercredis', 'Jeudis', 'Vendredis', 'Samedis'];
    return !recurrent ? '' : `Tâche se répétant tous les ${weekDays[jour]}`;
  };

    return (
        <Show actions={<ProtectedShowActions />}>
            <SimpleShowLayout>
                <DateField source="date" label="Date" />
                <TextField source="titre" label="Titre"/>
                <TextField source="description" label="Description"/>
                <BooleanField source="important" label="Important" />
                <BooleanField source="recurrent" label="Tache reccurente" />
                <FunctionField
                    source="reccurent"
                    label=""
                    render={record => getRecurrenceLabel(record) }
                    textAlign="right"
                />
                <BooleanField source="finished" label="Clôturé" />
            </SimpleShowLayout>
        </Show>
    );
}