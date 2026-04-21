export interface PublicReservationPayload {
  client: {
    name: string;
    logo: string | null;
    phone: string | null;
    lat: number | null;
    lng: number | null;
    timezone: string | null;
    address: string | null;
    zipcode: string | null;
    city: string | null;
    color: string | null;
  };
  reservation: {
    date: string | null;
    time: string | null;
    firstName: string | null;
    circuit: string | null;
  };
  briefing: {
    html: string | null;
    headerImage: string | null;
    showMap: boolean;
    extraContacts: string | null;
  } | null;
  circuitBriefing: {
    html: string | null;
    image: string | null;
  } | null;
}
