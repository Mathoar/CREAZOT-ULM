import { Menu, MenuItemLink, useSidebarState } from "react-admin";
import ClientSelector from '../ClientSelector';
import CommentIcon from "@mui/icons-material/Comment";
import GroupIcon from '@mui/icons-material/Group';
import FlightIcon from '@mui/icons-material/Flight';
import AirplaneTicketIcon from '@mui/icons-material/AirplaneTicket';
import BadgeIcon from '@mui/icons-material/Badge';
import FlightTakeoffIcon from '@mui/icons-material/FlightTakeoff';
import PublicIcon from '@mui/icons-material/Public';
import BuildIcon from '@mui/icons-material/Build';
import EditCalendarIcon from '@mui/icons-material/EditCalendar';
import AdminPanelSettingsIcon from '@mui/icons-material/AdminPanelSettings';
import CropOriginalIcon from '@mui/icons-material/CropOriginal';
import { useClient } from '../../admin/ClientProvider';
import { isDefined } from "../../../app/lib/utils";
import PermPhoneMsgIcon from '@mui/icons-material/PermPhoneMsg';
import { useState } from 'react';
import { Collapse } from '@mui/material';
import TuneIcon from '@mui/icons-material/Tune';
import FilterIcon from '@mui/icons-material/Filter';
import StoreIcon from '@mui/icons-material/Store';
import PersonIcon from '@mui/icons-material/Person';
import BusinessIcon from '@mui/icons-material/Business';
import CollectionsIcon from '@mui/icons-material/Collections';
import FlightLandIcon from '@mui/icons-material/FlightLand';
import PointOfSaleIcon from '@mui/icons-material/PointOfSale';
import CreditScoreIcon from '@mui/icons-material/CreditScore';
import ShoppingCartIcon from '@mui/icons-material/ShoppingCart';
import { useSessionContext } from "../../admin/SessionContextProvider";
import { clientUsingAvailabilityFilter, clientWithExpensesManagement } from "../../../app/lib/client";
import ConnectingAirportsIcon from '@mui/icons-material/ConnectingAirports';
import VideoCameraBackIcon from '@mui/icons-material/VideoCameraBack';
import InsertInvitationIcon from '@mui/icons-material/InsertInvitation';
import MonetizationOnIcon from '@mui/icons-material/MonetizationOn';
import CategoryIcon from '@mui/icons-material/Category';
import LayersIcon from '@mui/icons-material/Layers';
import ExtensionIcon from '@mui/icons-material/Extension';
import PriceChangeIcon from '@mui/icons-material/PriceChange';
import SubscriptionsIcon from '@mui/icons-material/Subscriptions';
import AssignmentIndIcon from "@mui/icons-material/AssignmentInd";
import PeopleIcon from "@mui/icons-material/People";
import SettingsApplicationsIcon from '@mui/icons-material/SettingsApplications';
import RadarIcon from '@mui/icons-material/Radar';
import FlagIcon from "@mui/icons-material/Flag";
import PercentIcon from "@mui/icons-material/Percent";
import GavelIcon from "@mui/icons-material/Gavel";
import SmartToyIcon from "@mui/icons-material/SmartToy";
import SettingsIcon from "@mui/icons-material/Settings";
import SmsIcon from "@mui/icons-material/Sms";
import MessageIcon from "@mui/icons-material/Message";
import MenuBookIcon from "@mui/icons-material/MenuBook";
import { Badge } from '@mui/material';
import { useAiReservationStats } from '../../../app/lib/mercure';
import BarChartIcon from '@mui/icons-material/BarChart';
import SchoolIcon from '@mui/icons-material/School';
import AutoStoriesIcon from '@mui/icons-material/AutoStories';
import AccountTreeIcon from '@mui/icons-material/AccountTree';
import HowToRegIcon from '@mui/icons-material/HowToReg';
import { clientWithTraining, clientWithManex } from "../../../app/lib/client";
import DescriptionIcon from "@mui/icons-material/Description";
import ReportProblemIcon from "@mui/icons-material/ReportProblem";

const CustomMenu = () => {

  const { session } = useSessionContext();
  const user = session?.user;
  const { client, isAdmin, isSuperAdmin } = useClient();
  const [superAdminOpen, setSuperAdminOpen] = useState(false);
  const [optionsOpen, setOptionsOpen] = useState(false);
  const [tarificationOpen, setTarificationOpen] = useState(false);
  const [parametresOpen, setParametresOpen] = useState(false);
  const [formationOpen, setFormationOpen] = useState(false);
  const [openSidebar] = useSidebarState();

  // Live badge powered by Mercure: replaces the previous 30s polling loop.
  // The hook performs an initial REST fetch then subscribes to per-client updates.
  const aiAssistantEnabled = !!(client && (client.hasAiReservationAssistant || client.hasVoiceAssistant));
  const stats = useAiReservationStats(client?.id, session?.accessToken, aiAssistantEnabled);
  const awaitingCount = stats.awaiting_club ?? 0;

  const handleSuperAdminClick = e => {
    e.preventDefault();
    setSuperAdminOpen(!superAdminOpen);
  };

  const handleOptionsClick = e => {
    e.preventDefault();
    setOptionsOpen(!optionsOpen);
  };

  const handleTarificationClick = e => {
    e.preventDefault();
    setTarificationOpen(!tarificationOpen);
  };

  const handleParametresClick = e => {
    e.preventDefault();
    setParametresOpen(!parametresOpen);
  };

  return (
    <Menu>
      <Menu.DashboardItem />
      { isAdmin &&
        <Menu.Item
          to="/analytics"
          primaryText="Statistiques"
          leftIcon={<BarChartIcon />}
        />
      }
      {/* @ts-ignore */}
      { (isDefined(client) && isDefined(client.hasReservation) && client.hasReservation) && isAdmin &&
        <Menu.Item
          to="/reservations"
          primaryText="Réservations"
          leftIcon={<EditCalendarIcon />}
        />
      }
      {/* @ts-ignore */}
      { (isDefined(client) && isDefined(client.hasReservation) && client.hasReservation && isDefined(client.hasPlanification) && client.hasPlanification) && isAdmin &&
        <Menu.Item
          to="/planning"
          primaryText="Planification"
          leftIcon={<SmsIcon />}
        />
      }
      {/* @ts-ignore */}
      { (isDefined(client) && (client.hasAiReservationAssistant || client.hasVoiceAssistant)) && isAdmin &&
        <Menu.Item
          to="/conversation_threads"
          primaryText={awaitingCount > 0 ? `Assistant IA (${awaitingCount})` : "Assistant IA"}
          leftIcon={
            <Badge badgeContent={awaitingCount} color="warning" max={99} invisible={awaitingCount === 0}>
              <SmartToyIcon />
            </Badge>
          }
          sx={awaitingCount > 0 ? { fontWeight: 700, backgroundColor: '#fff8e1' } : {}}
        />
      }
      {/* @ts-ignore */}
      { (isDefined(client) && isDefined(client.hasGifts) && client.hasGifts) && isAdmin &&
        <Menu.Item
          to="/cadeaux"
          primaryText="Prépaiements"
          leftIcon={<CreditScoreIcon />}
        />
      }
      {/* @ts-ignore */}
      { isAdmin && clientWithExpensesManagement(client) &&
        <Menu.Item
          to="/expenses"
          primaryText="Dépenses"
          leftIcon={<ShoppingCartIcon />}
        />
      }
      {/* @ts-ignore */}
      { isAdmin && isDefined(client) && isDefined(client.hasPaymentManagement) && client.hasPaymentManagement &&
        <Menu.Item
          to="/payments"
          primaryText="Paiements"
          leftIcon={<PointOfSaleIcon />}
        />
      }
      <Menu.Item
        to="/prestations"
        primaryText="Carnets de vols"
        leftIcon={<AirplaneTicketIcon />}
      />
      <Menu.Item
        to="/vols"
        primaryText="Vols"
        leftIcon={<FlightTakeoffIcon />}
      />
      {/* @ts-ignore */}
      { (isDefined(client) && isDefined(client.hasLandingManagement) && client.hasLandingManagement) && isAdmin &&
        <Menu.Item
          to="/landings"
          primaryText="Atterrissages"
          leftIcon={<FlightLandIcon />}
        />
      }
      
      { isDefined(client) && isDefined(client.hasPassengerRegistration) && client.hasPassengerRegistration && 
        <Menu.Item
          to="/passagers"
          primaryText="Passagers"
          leftIcon={<GroupIcon />}
        />
      }
      
      {/* @ts-ignore */}
      { isAdmin &&
        <Menu.Item
          to="/entretiens"
          primaryText="Maintenance"
          leftIcon={<BuildIcon />}
        />
      }
      {/* @ts-ignore */}
      { isAdmin &&
        <Menu.Item
          to="/aeronefs"
          primaryText="Aéronefs"
          leftIcon={<FlightIcon />}
        />
      }
      {/* @ts-ignore */}
      { isAdmin && clientUsingAvailabilityFilter(client) &&
        <Menu.Item
          to="/disponibilites"
          primaryText="Disponibilités"
          leftIcon={<InsertInvitationIcon />}
        />
      }
      {/* @ts-ignore */}
      { isAdmin &&
        <Menu.Item
          to="/profil_pilotes"
          primaryText="Pilotes"
          leftIcon={<BadgeIcon />}
        />
      }

      {/* Formation */}
      { isAdmin && clientWithTraining(client) &&
        <>
          <MenuItemLink
            to="#"
            onClick={e => { e.preventDefault(); setFormationOpen(!formationOpen); }}
            primaryText="Formation"
            leftIcon={<SchoolIcon className="h-[24px] w-[24px]"/>}
            dense={ !openSidebar }
            sx={{ cursor: 'pointer', backgroundColor: formationOpen ? '#EFF2F5' : '#F9FAFB' }}
          />
          <Collapse in={formationOpen} timeout="auto" unmountOnExit>
            <Menu.Item
              to="/lessons"
              primaryText="Leçons"
              leftIcon={<AutoStoriesIcon />}
              sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
            />
            <Menu.Item
              to="/programmes"
              primaryText="Programmes"
              leftIcon={<AccountTreeIcon />}
              sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
            />
            <Menu.Item
              to="/trainings"
              primaryText="Formations"
              leftIcon={<HowToRegIcon />}
              sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
            />
          </Collapse>
        </>
      }

      {/* MANEX */}
      { isAdmin && clientWithManex(client) &&
        <Menu.Item
          to="/manex"
          primaryText="MANEX"
          leftIcon={<DescriptionIcon />}
        />
      }

      {/* Événements de sécurité */}
      { isAdmin &&
        <Menu.Item
          to="/security_events"
          primaryText="Événements sécurité"
          leftIcon={<ReportProblemIcon />}
        />
      }

      {/* @ts-ignore */}
      { isAdmin &&
          <MenuItemLink
              to="#"
              onClick={ handleSuperAdminClick }
              primaryText="Administration"
              leftIcon={<TuneIcon className="h-[24px] w-[24px]"/>}
              dense={ !openSidebar }
              sx={{ cursor: 'pointer', backgroundColor: superAdminOpen ? '#EFF2F5' : '#F9FAFB' }}
          >
          </MenuItemLink>
      }
      {/* @ts-ignore */}
      { isAdmin &&
        <Collapse in={ superAdminOpen } timeout="auto" unmountOnExit>
            { isAdmin &&
              <Menu.Item
                to="/circuits"
                primaryText="Circuits"
                leftIcon={<PublicIcon />}
                sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
              />
            }  
            {/* @ts-ignore */}
            { isAdmin && isDefined(client) && isDefined(client.hasOptions) && client.hasOptions && 
              <Menu.Item
                to="/options"
                primaryText="Options"
                leftIcon={<CollectionsIcon />}
                sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
              />
            }
            { isAdmin &&
              <Menu.Item
                to="/airports"
                primaryText="Aéroports"
                leftIcon={<ConnectingAirportsIcon />}
                sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
              />
            }
            { isAdmin && isDefined(client) && client.hasOriginContact &&
              <Menu.Item
                to="/client-channels"
                primaryText="Canaux"
                leftIcon={<PermPhoneMsgIcon />}
                sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
              />
            }
            { isAdmin && isDefined(client) && client.hasCams &&
              <Menu.Item
                to="/cameras"
                primaryText="Caméras"
                leftIcon={<VideoCameraBackIcon />}
                sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
              />
            }
            { isAdmin && isDefined(client) && client.hasAI &&
              <Menu.Item
                to="/flight_rules"
                primaryText="Règles de vol"
                leftIcon={<GavelIcon />}
                sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
              />
            }
            { isAdmin && isDefined(client) && client.hasPlanification &&
              <Menu.Item
                to="/message_templates"
                primaryText="Modèles messages"
                leftIcon={<MessageIcon />}
                sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
              />
            }
            {/* @ts-ignore */}
            { (isDefined(client) && isDefined(client.hasPartners) && client.hasPartners) && isAdmin &&
              <Menu.Item
                to="/origines"
                primaryText="Partenaires"
                leftIcon={<StoreIcon />}
                sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
              />
            }
            { isAdmin && !isSuperAdmin && isDefined(client) &&
              <Menu.Item
                to={`/clients/${encodeURIComponent(client['@id'] || '/clients/' + client.id)}`}
                primaryText="Mon établissement"
                leftIcon={<BusinessIcon />}
                sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
              />
            }
            { isAdmin && isDefined(client) && client.hasPlanification && client.briefing &&
              <Menu.Item
                to={`/briefings/${encodeURIComponent(client.briefing['@id'] || '/briefings/' + client.briefing.id)}`}
                primaryText="Briefing"
                leftIcon={<MenuBookIcon />}
                sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
              />
            }
            { isSuperAdmin &&
              <Menu.Item
                to="/clients"
                primaryText="Établissements"
                leftIcon={<BusinessIcon />}
                sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
              />
            }
            { isAdmin &&
              <Menu.Item
                to="/members"
                primaryText="Membres"
                leftIcon={<PeopleIcon />}
                sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
              />
            }
            { isAdmin &&
              <Menu.Item
                to="/client_access_requests"
                primaryText="Demandes d'accès"
                leftIcon={<AssignmentIndIcon />}
                sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
              />
            }
            {/* @ts-ignore */}
            { isSuperAdmin &&
              <>
                <MenuItemLink
                    to="#"
                    onClick={ handleParametresClick }
                    primaryText="Paramètres"
                    leftIcon={<SettingsIcon className="h-[24px] w-[24px]"/>}
                    dense={ !openSidebar }
                    sx={{ cursor: 'pointer', pl: 3, backgroundColor: parametresOpen ? '#E4E7EB' : '#EFF2F5' }}
                >
                </MenuItemLink>
                <Collapse in={ parametresOpen } timeout="auto" unmountOnExit>
                    <Menu.Item
                      to="/natures"
                      primaryText="Natures"
                      leftIcon={<CommentIcon />}
                      sx={{ pl: 2, backgroundColor: '#E4E7EB' }}
                    />
                    <Menu.Item
                      to="/contacts"
                      primaryText="Canaux (définition)"
                      leftIcon={<PermPhoneMsgIcon />}
                      sx={{ pl: 2, backgroundColor: '#E4E7EB' }}
                    />

                    <Menu.Item
                      to="/qualifications"
                      primaryText="Qualifications"
                      leftIcon={<AdminPanelSettingsIcon />}
                      sx={{ pl: 2, backgroundColor: '#E4E7EB' }}
                    />
                    <Menu.Item
                      to="/country_codes"
                      primaryText="Codes pays"
                      leftIcon={<FlagIcon />}
                      sx={{ pl: 2, backgroundColor: '#E4E7EB' }}
                    />
                    <Menu.Item
                      to="/tax_rates"
                      primaryText="Taux de TVA"
                      leftIcon={<PercentIcon />}
                      sx={{ pl: 2, backgroundColor: '#E4E7EB' }}
                    />
                    <Menu.Item
                      to="/icao_references"
                      primaryText="Codes ICAO"
                      leftIcon={<RadarIcon />}
                      sx={{ pl: 2, backgroundColor: '#E4E7EB' }}
                    />
                    <Menu.Item
                      to="/site-settings"
                      primaryText="Paramétrage SaaS"
                      leftIcon={<SettingsApplicationsIcon />}
                      sx={{ pl: 2, backgroundColor: '#E4E7EB' }}
                    />
                    <Menu.Item
                      to="/integration_patterns"
                      primaryText="Intégrations API"
                      leftIcon={<ExtensionIcon />}
                      sx={{ pl: 2, backgroundColor: '#E4E7EB' }}
                    />
                </Collapse>
              </>
            }
        </Collapse>
      }

      { isSuperAdmin &&
          <MenuItemLink
              to="#"
              onClick={ handleTarificationClick }
              primaryText="Tarification"
              leftIcon={<MonetizationOnIcon className="h-[24px] w-[24px]"/>}
              dense={ !openSidebar }
              sx={{ cursor: 'pointer', backgroundColor: tarificationOpen ? '#EFF2F5' : '#F9FAFB' }}
          >
          </MenuItemLink>
      }
      { isSuperAdmin &&
        <Collapse in={ tarificationOpen } timeout="auto" unmountOnExit>
            <Menu.Item
              to="/pricing-categories"
              primaryText="Grilles tarifaires"
              leftIcon={<CategoryIcon />}
              sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
            />
            <Menu.Item
              to="/pricing-tiers"
              primaryText="Paliers"
              leftIcon={<LayersIcon />}
              sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
            />
            <Menu.Item
              to="/module-packs"
              primaryText="Packs de modules"
              leftIcon={<ExtensionIcon />}
              sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
            />
            <Menu.Item
              to="/module-pack-prices"
              primaryText="Prix des packs"
              leftIcon={<PriceChangeIcon />}
              sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
            />
            <Menu.Item
              to="/subscriptions"
              primaryText="Abonnements"
              leftIcon={<SubscriptionsIcon />}
              sx={{ pl: 3, backgroundColor: '#EFF2F5' }}
            />
        </Collapse>
      }

      <div style={{ flexGrow: 1 }} />
      <ClientSelector />
    </Menu>
  );
};

export default CustomMenu;
