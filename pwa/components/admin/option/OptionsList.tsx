import { type NextPage } from "next";
import {
  Datagrid,
  List,
  TextField,
  ExportButton,
  TopToolbar,
  SimpleList,
  NumberField,
  ShowButton,
  BooleanField,
} from "react-admin";
import { useMercure } from "../../../utils/mercure";
import { type Contact } from "../../../types/Contact";
import { useMediaQuery, Theme } from '@mui/material';
import { type PagedCollection } from "../../../types/collection";
import { isDefined } from "../../../app/lib/utils";
import { ProtectedCreateButton, ProtectedEditButton } from "../PermissionGuards";


export interface Props {
  data: PagedCollection<Contact> | null;
  hubURL: string | null;
  page: number;
}

const ListActions = () => (
  <TopToolbar>
      <ProtectedCreateButton/>
      <ExportButton/>
  </TopToolbar>
);

export const OptionsList: NextPage<Props> = ({ data, hubURL, page }) => {
  const collection = useMercure(data, hubURL);
  const isSmall = useMediaQuery<Theme>(theme => theme.breakpoints.down('sm'));

  return (
    <List resource="options" actions={<ListActions/>}>
        { isSmall ? 
            <SimpleList
              primaryText={ record => record.nom }
              tertiaryText={ record => isDefined(record.prix) ? record.prix.toFixed(2) + ' €' : "" }
              linkType="edit"
            /> 
            :
            <Datagrid sx={{ '& .RaDatagrid-headerCell': {backgroundColor: '#ededed', fontWeight: "lighter"}}}>
                <TextField source="name" label="Nom de l'option" sortable={ true }/>
                <NumberField source="prix" label="Prix" options={{ style: 'currency', currency: 'EUR' }}/>
                <BooleanField source="isAvailable" label="Disponible" />
                <p className="text-right">
                    <ShowButton />
                    <ProtectedEditButton />
                </p>
            </Datagrid>
        }
    </List>
  );
}
