/**
 * Dynamic Agricultural Shipping & Logistics Calculator
 * Calculates freight & delivery costs based on product origin (e.g., Adama, Hawassa, Bahir Dar),
 * user destination, total weight (kg), distance (km), and product perishability.
 * 
 * Required Business Logic (Weight-Tiered Shipping):
 * 1. Small Shipments / Light Loads (Weight <= 5 kg):
 *    - Distance matrix is ignored (distance km rate is NOT multiplied).
 *    - Applies fixed affordable flat rate based on selected shipping type:
 *      * Standard: ETB 350 flat rate
 *      * Express: ETB 600 flat rate
 *      * Cold-Chain: ETB 1200 flat rate
 * 2. Heavy Shipments / Bulk Loads (Weight > 5 kg):
 *    - Full Freight Formula: [Base Fee + (Weight * Rate per kg) + (Distance * Rate per km)]
 *    - Required due to truck allocation and higher logistics/fuel costs.
 */

export interface CartItem {
  id: number
  product_id?: number
  product_name?: string
  quantity: number
  unit?: string
  unit_price?: number
  price?: number
  subtotal?: number
  category?: string
  origin_location?: string
  product?: {
    category?: string
    name?: string
    unit?: string
    quantity_unit?: string
    origin_location?: string
  }
}

export interface RegionLogisticsInfo {
  region: string
  distanceKm: number
  multiplier: number
  transitDays: string
}

export interface ShippingBreakdown {
  baseFee: number
  weightKg: number
  ratePerKg: number
  weightCost: number
  originLocation: string
  destinationLocation: string
  distanceKm: number
  ratePerKm: number
  distanceCost: number
  isFlatRate: boolean
  flatRate: number
  totalFee: number
  formulaStr: string
}

export interface OriginShippingRoute {
  origin: string
  destination: string
  distanceKm: number
  weightKg: number
  itemCount: number
  items: any[]
  hasPerishables: boolean
  fee: number
  isFlatRate: boolean
  formulaStr: string
  breakdown: ShippingBreakdown
}

export interface OriginGroup {
  origin: string
  destination: string
  distanceKm: number
  weightKg: number
  hasPerishables: boolean
  items: any[]
}

export interface DynamicShippingOption {
  key: 'standard' | 'express' | 'overnight' | 'cold_chain'
  title: string
  desc: string
  fee: number
  badge: string | null
  isPerishableOnly: boolean
  transitDays: string
  requiresColdChain?: boolean
  estimatedDays: string
  breakdown?: ShippingBreakdown
  routes?: OriginShippingRoute[]
  isMultiOrigin?: boolean
  originCount?: number
}

export const WEIGHT_FLAT_THRESHOLD_KG = 5.0
export const DISTANCE_SHORT_THRESHOLD_KM = 30.0

/**
 * Major Ethiopian Agricultural Hubs & Production Basins
 */
export const ETHIOPIAN_ORIGIN_HUBS: { name: string; region: string; distToAddis: number; label: string }[] = [
  { name: 'Adama', region: 'Oromia', distToAddis: 95, label: 'Adama (Oromia Agro-Hub)' },
  { name: 'Hawassa', region: 'Sidama', distToAddis: 275, label: 'Hawassa (Sidama Rift Valley)' },
  { name: 'Bahir Dar', region: 'Amhara', distToAddis: 495, label: 'Bahir Dar (Lake Tana Basin)' },
  { name: 'Addis Ababa', region: 'Addis Ababa', distToAddis: 25, label: 'Addis Ababa (Central Terminal)' },
  { name: 'Jimma', region: 'Oromia', distToAddis: 350, label: 'Jimma (Coffee & Agro Basin)' },
  { name: 'Gondar', region: 'Amhara', distToAddis: 660, label: 'Gondar (North Amhara Basin)' },
  { name: 'Dire Dawa', region: 'Dire Dawa', distToAddis: 450, label: 'Dire Dawa (Eastern Corridor)' },
  { name: 'Mekelle', region: 'Tigray', distToAddis: 780, label: 'Mekelle (Tigray Basin)' },
  { name: 'Shashamane', region: 'Oromia', distToAddis: 245, label: 'Shashamane (Southern Junction)' },
  { name: 'Nekemte', region: 'Oromia', distToAddis: 330, label: 'Nekemte (Western Grain Belt)' },
  { name: 'Bishoftu', region: 'Oromia', distToAddis: 45, label: 'Bishoftu (Central Agro Valley)' },
  { name: 'Arba Minch', region: 'SNNPR', distToAddis: 440, label: 'Arba Minch (Fruit Valley)' },
  { name: 'Dessie', region: 'Amhara', distToAddis: 385, label: 'Dessie (Eastern Amhara Basin)' },
  { name: 'Debre Markos', region: 'Amhara', distToAddis: 300, label: 'Debre Markos (Gojjam Basin)' }
]

export const DIRECT_DISTANCES: Record<string, number> = {
  'Adama:Addis Ababa': 95,
  'Addis Ababa:Adama': 95,
  'Adama:Hawassa': 185,
  'Hawassa:Adama': 185,
  'Adama:Dire Dawa': 355,
  'Dire Dawa:Adama': 355,
  'Hawassa:Addis Ababa': 275,
  'Addis Ababa:Hawassa': 275,
  'Hawassa:Shashamane': 25,
  'Shashamane:Hawassa': 25,
  'Bahir Dar:Addis Ababa': 495,
  'Addis Ababa:Bahir Dar': 495,
  'Bahir Dar:Gondar': 175,
  'Gondar:Bahir Dar': 175,
  'Bishoftu:Addis Ababa': 45,
  'Addis Ababa:Bishoftu': 45,
  'Bishoftu:Adama': 50,
  'Adama:Bishoftu': 50,
  'Jimma:Addis Ababa': 350,
  'Addis Ababa:Jimma': 350,
  'Dire Dawa:Harari': 55,
  'Harari:Dire Dawa': 55,
  'Dire Dawa:Addis Ababa': 450,
  'Addis Ababa:Dire Dawa': 450,
  'Mekelle:Addis Ababa': 780,
  'Addis Ababa:Mekelle': 780,
  'Arba Minch:Hawassa': 165,
  'Hawassa:Arba Minch': 165,
}

export const ETHIOPIAN_REGIONS_LOGISTICS: Record<string, RegionLogisticsInfo> = {
  'Addis Ababa': {
    region: 'Addis Ababa',
    distanceKm: 25,
    multiplier: 1.0,
    transitDays: 'Same-day or next-day delivery'
  },
  'Oromia': {
    region: 'Oromia',
    distanceKm: 120,
    multiplier: 1.25,
    transitDays: '1 - 2 business days'
  },
  'Amhara': {
    region: 'Amhara',
    distanceKm: 450,
    multiplier: 1.55,
    transitDays: '2 - 3 business days'
  },
  'Sidama': {
    region: 'Sidama',
    distanceKm: 275,
    multiplier: 1.35,
    transitDays: '1 - 2 business days'
  },
  'SNNPR': {
    region: 'SNNPR',
    distanceKm: 380,
    multiplier: 1.45,
    transitDays: '2 - 3 business days'
  },
  'Dire Dawa': {
    region: 'Dire Dawa',
    distanceKm: 480,
    multiplier: 1.6,
    transitDays: '2 - 3 business days'
  },
  'Harari': {
    region: 'Harari',
    distanceKm: 510,
    multiplier: 1.65,
    transitDays: '2 - 4 business days'
  },
  'Afar': {
    region: 'Afar',
    distanceKm: 580,
    multiplier: 1.75,
    transitDays: '3 - 4 business days'
  },
  'Benishangul-Gumuz': {
    region: 'Benishangul-Gumuz',
    distanceKm: 620,
    multiplier: 1.8,
    transitDays: '3 - 5 business days'
  },
  'Tigray': {
    region: 'Tigray',
    distanceKm: 780,
    multiplier: 1.95,
    transitDays: '3 - 5 business days'
  },
  'Somali': {
    region: 'Somali',
    distanceKm: 630,
    multiplier: 1.9,
    transitDays: '3 - 5 business days'
  },
  'Gambela': {
    region: 'Gambela',
    distanceKm: 770,
    multiplier: 1.95,
    transitDays: '4 - 5 business days'
  }
}

export const ETHIOPIAN_REGIONS = Object.keys(ETHIOPIAN_REGIONS_LOGISTICS)

export interface DeliveryDestinationOption {
  city: string
  region: string
  label: string
  zone?: string
}

/**
 * Detailed Delivery Destinations across Ethiopia capturing both specific City/Hub and Region
 */
export const ETHIOPIAN_DELIVERY_DESTINATIONS: DeliveryDestinationOption[] = [
  { city: 'Addis Ababa', region: 'Addis Ababa', label: 'Addis Ababa (Central Terminal)', zone: 'Bole Sub-City' },
  { city: 'Adama', region: 'Oromia', label: 'Adama (Oromia Agro-Hub)', zone: 'East Shewa Zone' },
  { city: 'Bishoftu', region: 'Oromia', label: 'Bishoftu (Central Agro Valley)', zone: 'East Shewa Zone' },
  { city: 'Jimma', region: 'Oromia', label: 'Jimma (Coffee & Agro Basin)', zone: 'Jimma Zone' },
  { city: 'Shashamane', region: 'Oromia', label: 'Shashamane (Southern Junction)', zone: 'West Arsi Zone' },
  { city: 'Nekemte', region: 'Oromia', label: 'Nekemte (Western Grain Belt)', zone: 'East Wollega Zone' },
  { city: 'Bahir Dar', region: 'Amhara', label: 'Bahir Dar (Lake Tana Basin)', zone: 'West Gojjam Zone' },
  { city: 'Gondar', region: 'Amhara', label: 'Gondar (North Amhara Basin)', zone: 'Central Gondar Zone' },
  { city: 'Dessie', region: 'Amhara', label: 'Dessie (Eastern Amhara Basin)', zone: 'South Wollo Zone' },
  { city: 'Debre Markos', region: 'Amhara', label: 'Debre Markos (Gojjam Basin)', zone: 'East Gojjam Zone' },
  { city: 'Hawassa', region: 'Sidama', label: 'Hawassa (Sidama Rift Valley)', zone: 'Hawassa City Zone' },
  { city: 'Arba Minch', region: 'SNNPR', label: 'Arba Minch (Fruit Valley)', zone: 'Gamo Zone' },
  { city: 'Dire Dawa', region: 'Dire Dawa', label: 'Dire Dawa (Eastern Corridor)', zone: 'Dire Dawa Administration' },
  { city: 'Harar', region: 'Harari', label: 'Harar (Harari Regional Hub)', zone: 'Harar Administration' },
  { city: 'Mekelle', region: 'Tigray', label: 'Mekelle (Tigray Basin)', zone: 'Central Tigray' },
  { city: 'Semera', region: 'Afar', label: 'Semera (Afar Regional Hub)', zone: 'Awsi Rasu (Zone 1)' },
  { city: 'Jijiga', region: 'Somali', label: 'Jijiga (Somali Regional Hub)', zone: 'Fafan Zone' },
  { city: 'Asosa', region: 'Benishangul-Gumuz', label: 'Asosa (B-Gumuz Regional Hub)', zone: 'Asosa Zone' },
  { city: 'Gambela', region: 'Gambela', label: 'Gambela (Gambela Regional Hub)', zone: 'Anywaa Zone' }
]

/**
 * Resolves destination input into specific hub/city and administrative region
 */
export function resolveDestinationHubAndRegion(destInput?: string): { city: string; region: string; hubName: string; displayName: string; defaultZone: string } {
  if (!destInput) {
    return { city: 'Addis Ababa', region: 'Addis Ababa', hubName: 'Addis Ababa', displayName: 'Addis Ababa (Addis Ababa)', defaultZone: 'Bole Sub-City' }
  }
  const raw = String(destInput).trim()

  // 1. Direct match with ETHIOPIAN_DELIVERY_DESTINATIONS
  const directMatch = ETHIOPIAN_DELIVERY_DESTINATIONS.find(
    d => d.city.toLowerCase() === raw.toLowerCase() ||
         d.label.toLowerCase() === raw.toLowerCase() ||
         `${d.city}, ${d.region}`.toLowerCase() === raw.toLowerCase()
  )
  if (directMatch) {
    return {
      city: directMatch.city,
      region: directMatch.region,
      hubName: directMatch.city,
      displayName: `${directMatch.city} (${directMatch.region})`,
      defaultZone: directMatch.zone || 'Central Zone'
    }
  }

  // 2. Direct match with ETHIOPIAN_ORIGIN_HUBS
  const hubMatch = ETHIOPIAN_ORIGIN_HUBS.find(h => h.name.toLowerCase() === raw.toLowerCase())
  if (hubMatch) {
    return {
      city: hubMatch.name,
      region: hubMatch.region,
      hubName: hubMatch.name,
      displayName: `${hubMatch.name} (${hubMatch.region})`,
      defaultZone: `${hubMatch.name} Zone`
    }
  }

  // 3. String formatted as "City (Region)" or "City, Region"
  const parsed = raw.match(/^([^,(]+)(?:[,\(]\s*([^\)]+)\)?)?$/)
  if (parsed) {
    const p1 = parsed[1].trim()
    const p2 = parsed[2] ? parsed[2].trim() : ''

    const foundByP1 = ETHIOPIAN_DELIVERY_DESTINATIONS.find(d => d.city.toLowerCase() === p1.toLowerCase())
    if (foundByP1) {
      return {
        city: foundByP1.city,
        region: p2 || foundByP1.region,
        hubName: foundByP1.city,
        displayName: `${foundByP1.city} (${p2 || foundByP1.region})`,
        defaultZone: foundByP1.zone || 'Central Zone'
      }
    }

    if (ETHIOPIAN_REGIONS_LOGISTICS[p1]) {
      const hubInRegion = ETHIOPIAN_DELIVERY_DESTINATIONS.find(d => d.region.toLowerCase() === p1.toLowerCase())
      return {
        city: hubInRegion?.city || p1,
        region: p1,
        hubName: hubInRegion?.city || p1,
        displayName: `${hubInRegion?.city || p1} (${p1})`,
        defaultZone: hubInRegion?.zone || `${p1} Central Zone`
      }
    }
  }

  // 4. Matches region name
  if (ETHIOPIAN_REGIONS_LOGISTICS[raw]) {
    const hubInRegion = ETHIOPIAN_DELIVERY_DESTINATIONS.find(d => d.region.toLowerCase() === raw.toLowerCase())
    return {
      city: hubInRegion?.city || raw,
      region: raw,
      hubName: hubInRegion?.city || raw,
      displayName: `${hubInRegion?.city || raw} (${raw})`,
      defaultZone: hubInRegion?.zone || `${raw} Central Zone`
    }
  }

  return { city: raw, region: raw, hubName: raw, displayName: raw, defaultZone: 'Central Area' }
}

/**
 * Formats a clean hierarchical location string from address fields:
 * [Sub-City/Woreda], [City/Town], [Zone], [Region], [Country]
 * Strictly deduplicates overlapping/repeated variables (e.g. eliminates "Central, Addis Ababa, Addis Ababa")
 */
export function formatHierarchicalLocation(addr: {
  sub_city?: string
  woreda_kebele?: string
  woreda?: string
  city?: string
  zone?: string
  region?: string
  country?: string
}): string {
  if (!addr) return ''

  const subCity = (addr.sub_city || '').trim()
  const woreda = (addr.woreda_kebele || addr.woreda || '').trim()
  const city = (addr.city || '').trim()
  const zone = (addr.zone || '').trim()
  const region = (addr.region || '').trim()
  const country = (addr.country || '').trim()

  const segments: string[] = []

  const isRedundant = (candidate: string): boolean => {
    if (!candidate || !candidate.trim()) return true
    const candNorm = candidate.toLowerCase().trim()
    return segments.some(seg => {
      const segNorm = seg.toLowerCase().trim()
      return segNorm === candNorm ||
             segNorm.includes(candNorm) ||
             candNorm.includes(segNorm)
    })
  }

  // 1. [Sub-City/Woreda]
  const subWoredaParts: string[] = []
  if (subCity) {
    subWoredaParts.push(subCity)
  }
  if (woreda && !subWoredaParts.some(p => p.toLowerCase().includes(woreda.toLowerCase()))) {
    subWoredaParts.push(woreda)
  }
  if (subWoredaParts.length > 0) {
    segments.push(subWoredaParts.join(', '))
  }

  // 2. [City/Town]
  if (city && !isRedundant(city)) {
    segments.push(city)
  }

  // 3. [Zone]
  if (zone && !isRedundant(zone)) {
    segments.push(zone)
  }

  // 4. [Region] (e.g. eliminates repeating "Addis Ababa" when city is already "Addis Ababa")
  if (region && !isRedundant(region)) {
    segments.push(region)
  }

  // 5. [Country] (if provided and not redundant)
  if (country && !isRedundant(country)) {
    segments.push(country)
  }

  return segments.join(', ')
}

/**
 * Cleanly formats address in strict hierarchical order:
 * [Specific Street], [Woreda], [Zone/Sub-City], [Region], [Country]
 * Eliminates redundant repetitions and jumbled fields (e.g. "Addis Ababa, Ethiopia, Bole, Central, Oromia")
 */
export function formatHierarchicalAddress(params: {
  street?: string
  street_address?: string
  woreda?: string
  woreda_kebele?: string
  sub_city?: string
  zone?: string
  region?: string
  country?: string
  city?: string
}): string {
  let street = (params.street || params.street_address || '').trim()
  const locationHierarchy = formatHierarchicalLocation({
    sub_city: params.sub_city,
    woreda: params.woreda || params.woreda_kebele,
    city: params.city,
    zone: params.zone,
    region: params.region,
    country: params.country || 'Ethiopia'
  })

  // Clean generic country artifact from street without overriding with hardcoded defaults
  if (street) {
    street = street.replace(/^[,.\s]+|[,.\s]+$/g, '')
    if (/^(addis ababa,?\s*ethiopia|ethiopia)$/i.test(street)) {
      street = ''
    } else {
      // Remove trailing ", Ethiopia" from street
      street = street.replace(/,\s*ethiopia$/i, '').trim()
    }
  }

  if (street && locationHierarchy) {
    if (street.toLowerCase().includes(locationHierarchy.toLowerCase())) {
      return street
    }
    return `${street}, ${locationHierarchy}`
  }

  return street || locationHierarchy || [params.city, params.region, params.country || 'Ethiopia'].filter(Boolean).join(', ') || 'Ethiopia'
}

export const PERISHABLE_CATEGORIES = [
  'vegetables',
  'vegetable',
  'fruits',
  'fruit',
  'dairy',
  'milk',
  'meat',
  'poultry',
  'fish',
  'fresh produce',
  'horticulture',
  'flowers'
]

export const NON_PERISHABLE_CATEGORIES = [
  'grains',
  'grain',
  'cereals',
  'cereal',
  'pulses',
  'pulse',
  'legumes',
  'seeds',
  'seed',
  'coffee',
  'spices',
  'spice',
  'oilseeds',
  'dry goods',
  'flour'
]

/**
 * Calculates dynamic distance (km) between product origin hub and buyer destination
 */
export function calculateOriginDestinationDistanceKm(origin?: string, destination?: string): number {
  const orig = String(origin || 'Addis Ababa').trim()
  const destRaw = String(destination || 'Addis Ababa').trim()

  const resolvedDest = resolveDestinationHubAndRegion(destRaw)
  const destHubName = resolvedDest.hubName
  const destRegion = resolvedDest.region

  if (orig.toLowerCase() === destHubName.toLowerCase() || orig.toLowerCase() === destRaw.toLowerCase()) {
    return 25
  }

  // Try direct key matches (raw, city hub, region)
  const candidateKeys = [
    `${orig}:${destRaw}`,
    `${destRaw}:${orig}`,
    `${orig}:${destHubName}`,
    `${destHubName}:${orig}`,
    `${orig}:${destRegion}`,
    `${destRegion}:${orig}`
  ]
  for (const k of candidateKeys) {
    if (DIRECT_DISTANCES[k]) return DIRECT_DISTANCES[k]
  }

  const origHub = ETHIOPIAN_ORIGIN_HUBS.find(h => h.name.toLowerCase() === orig.toLowerCase())
  const origDistToAddis = origHub ? origHub.distToAddis : 25
  const origRegion = origHub ? origHub.region : orig

  if (ETHIOPIAN_REGIONS_LOGISTICS[destRegion]) {
    const destRegionData = ETHIOPIAN_REGIONS_LOGISTICS[destRegion]
    const destDistToAddis = destRegionData.distanceKm

    if (origRegion.toLowerCase() === destRegion.toLowerCase()) {
      return Math.min(60, Math.round(destDistToAddis * 0.4))
    }
    if (destRegion.toLowerCase() === 'addis ababa') {
      return Math.max(25, origDistToAddis)
    }
    if (orig.toLowerCase() === 'addis ababa') {
      return Math.max(25, destDistToAddis)
    }
    return Math.round(origDistToAddis + (destDistToAddis * 0.85))
  }

  const destHub = ETHIOPIAN_ORIGIN_HUBS.find(h => h.name.toLowerCase() === destHubName.toLowerCase())
  if (destHub) {
    if (origRegion.toLowerCase() === destHub.region.toLowerCase()) {
      return 40
    }
    return Math.round(origDistToAddis + destHub.distToAddis)
  }

  return 95
}

/**
 * Resolves effective origin location and distance from cart items to destination
 */
export function resolveCartOriginAndDistance(cartItems: any[], destination = 'Addis Ababa'): {
  origin: string
  allOrigins: string[]
  distanceKm: number
  destination: string
} {
  let maxDistance = 25
  let primaryOrigin = 'Addis Ababa'
  const originsFound: string[] = []

  if (Array.isArray(cartItems)) {
    for (const item of cartItems) {
      const orig = item?.origin_location || item?.product?.origin_location || 'Addis Ababa'
      originsFound.push(orig)
      const dist = calculateOriginDestinationDistanceKm(orig, destination)
      if (dist >= maxDistance) {
        maxDistance = dist
        primaryOrigin = orig
      }
    }
  }

  return {
    origin: primaryOrigin,
    allOrigins: Array.from(new Set(originsFound)),
    distanceKm: maxDistance,
    destination
  }
}

/**
 * Checks if a cart item is perishable (requires temperature control)
 */
export function isItemPerishable(item: any): boolean {
  if (!item) return false
  const cat = String(item.product?.category || item.category || '').toLowerCase().trim()
  const name = String(item.product_name || item.product?.name || item.name || '').toLowerCase()

  if (PERISHABLE_CATEGORIES.some(c => cat.includes(c))) return true

  const perishableKeywords = [
    'tomato', 'onion', 'potato', 'cabbage', 'carrot', 'pepper', 'spinach',
    'lettuce', 'banana', 'mango', 'avocado', 'apple', 'orange', 'milk',
    'cheese', 'egg', 'meat', 'beef', 'chicken', 'fish', 'strawberry'
  ]
  return perishableKeywords.some(k => name.includes(k))
}

/**
 * Calculate normalized weight in kilograms for a single item
 */
export function getItemWeightKg(item: any): number {
  if (!item) return 0
  const rawUnit = String(item.unit || item.product?.unit || item.product?.quantity_unit || 'kg').toLowerCase().trim()
  const qty = Number(item.quantity) || 1

  const unit = rawUnit.replace(/^(etb|birr|\$)?\s*\/?\s*/i, '').trim()

  switch (unit) {
    case 'kg':
    case 'kilogram':
    case 'kilograms':
      return qty
    case 'g':
    case 'gram':
    case 'grams':
      return qty * 0.001
    case 'quintal':
    case 'quintals':
    case 'qtl':
      return qty * 100
    case 'ton':
    case 'tonne':
    case 'tons':
      return qty * 1000
    case 'crate':
    case 'crates':
      return qty * 25
    case 'bag':
    case 'bags':
    case 'sack':
    case 'sacks':
      return qty * 50
    case 'bundle':
    case 'bundles':
      return qty * 5
    case 'piece':
    case 'pieces':
      return qty * 2
    case 'liter':
    case 'liters':
      return qty * 1
    default:
      return qty * 1
  }
}

/**
 * Total cart weight in kilograms
 */
export function getCartTotalWeightKg(cartItems: any[]): number {
  if (!Array.isArray(cartItems) || cartItems.length === 0) return 0
  return cartItems.reduce((sum, item) => sum + getItemWeightKg(item), 0)
}

/**
 * Check if the cart contains any perishable items
 */
export function cartHasPerishables(cartItems: any[]): boolean {
  if (!Array.isArray(cartItems) || cartItems.length === 0) return false
  return cartItems.some(item => isItemPerishable(item))
}

/**
 * Group cart items by their respective agricultural origin location
 */
export function groupCartItemsByOrigin(
  cartItems: any[],
  destination = 'Addis Ababa'
): OriginGroup[] {
  if (!Array.isArray(cartItems) || cartItems.length === 0) return []

  const groups: Record<string, { items: any[]; totalWeight: number; hasPerishables: boolean }> = {}

  for (const item of cartItems) {
    if (!item) continue
    const orig = String(
      item.origin_location ||
      item.product?.origin_location ||
      'Addis Ababa'
    ).trim() || 'Addis Ababa'

    if (!groups[orig]) {
      groups[orig] = { items: [], totalWeight: 0, hasPerishables: false }
    }

    groups[orig].items.push(item)
    groups[orig].totalWeight += getItemWeightKg(item)
    if (isItemPerishable(item)) {
      groups[orig].hasPerishables = true
    }
  }

  return Object.entries(groups).map(([origin, data]) => ({
    origin,
    destination,
    distanceKm: calculateOriginDestinationDistanceKm(origin, destination),
    weightKg: Math.max(1, Math.round(data.totalWeight * 100) / 100),
    hasPerishables: data.hasPerishables,
    items: data.items
  }))
}

/**
 * Weight-Tiered Shipping Calculation Helper
 * 
 * Handles business rules for:
 * 1. Small Shipments / Light Loads (totalWeight <= 5 kg):
 *    - Distance is ignored (no distance km multiplier applied).
 *    - Applies fixed affordable flat rate based on selected shipping type:
 *      * Standard: ETB 350
 *      * Express: ETB 600
 *      * Cold-Chain: ETB 1200
 * 2. Heavy Shipments / Bulk Loads (totalWeight > 5 kg):
 *    - Dynamic Freight Formula: Base Fee + (Weight * Rate per kg) + (Distance * Rate per km)
 *    - Rounded to nearest 10 ETB, with flat rate as minimum floor.
 */
export function calculateTierFee(
  totalWeight: number,
  distance: number,
  origin: string,
  destination: string,
  config: {
    flatRate: number
    baseFee: number
    ratePerKg: number
    ratePerKm: number
  }
): { fee: number; breakdown: ShippingBreakdown } {
  // -------------------------------------------------------------------------
  // THRESHOLD CHECK: Light Loads (<= 5 kg) vs Bulk / Heavy Shipments (> 5 kg)
  // -------------------------------------------------------------------------
  const isLightLoad = totalWeight <= WEIGHT_FLAT_THRESHOLD_KG

  if (isLightLoad) {
    // 1. Small Shipments (<= 5 kg):
    // Do NOT multiply by distance matrix (ignore distance km rate).
    // Charge a fixed, affordable flat rate based on shipping tier.
    return {
      fee: config.flatRate,
      breakdown: {
        baseFee: config.flatRate,
        weightKg: totalWeight,
        ratePerKg: config.ratePerKg,
        weightCost: 0,
        originLocation: origin,
        destinationLocation: destination,
        distanceKm: distance,
        ratePerKm: config.ratePerKm,
        distanceCost: 0,
        isFlatRate: true,
        flatRate: config.flatRate,
        totalFee: config.flatRate,
        formulaStr: ''
      }
    }
  }

  // 2. Heavy Shipments / Bulk Loads (> 5 kg):
  // Shipments exceeding 5 kg require truck allocation and higher logistics/fuel costs.
  // Formula: Base Fee + (Weight * Rate per kg) + (Distance * Rate per km)
  const weightCost = Math.round(totalWeight * config.ratePerKg)
  const distanceCost = Math.round(distance * config.ratePerKm)
  const rawTotal = config.baseFee + weightCost + distanceCost
  
  // Ensure fee never falls below the baseline flat rate, rounded to nearest 10 ETB
  const finalFee = Math.max(config.flatRate, Math.round(rawTotal / 10) * 10)

  return {
    fee: finalFee,
    breakdown: {
      baseFee: config.baseFee,
      weightKg: totalWeight,
      ratePerKg: config.ratePerKg,
      weightCost,
      originLocation: origin,
      destinationLocation: destination,
      distanceKm: distance,
      ratePerKm: config.ratePerKm,
      distanceCost,
      isFlatRate: false,
      flatRate: config.flatRate,
      totalFee: finalFee,
      formulaStr: `Base ETB ${config.baseFee} + (${totalWeight}kg × ETB ${config.ratePerKg}) + (${distance}km to ${destination} × ETB ${config.ratePerKm}) = ETB ${finalFee}`
    }
  }
}

/**
 * Multi-Origin Split Shipping Calculator
 * 
 * Groups cart items by their distinct origins, calculates each route's fee
 * independently using weight-tiered logic (<= 5kg flat rate vs > 5kg dynamic matrix),
 * and sums the route fees to determine the total cart shipping fee.
 */
export function getDynamicShippingOptions(
  cartItems: any[],
  destinationName = 'Addis Ababa',
  forcedOrigin?: string
): DynamicShippingOption[] {
  let groups = groupCartItemsByOrigin(cartItems, destinationName)
  const hasPerishables = cartHasPerishables(cartItems)
  const resolvedDest = resolveDestinationHubAndRegion(destinationName)
  const regionInfo = ETHIOPIAN_REGIONS_LOGISTICS[resolvedDest.region] || ETHIOPIAN_REGIONS_LOGISTICS['Addis Ababa']

  // If forcedOrigin is provided and cart is empty or single forced override
  if (groups.length === 0 && forcedOrigin) {
    groups = [{
      origin: forcedOrigin,
      destination: destinationName,
      distanceKm: calculateOriginDestinationDistanceKm(forcedOrigin, destinationName),
      weightKg: Math.max(1, getCartTotalWeightKg(cartItems)),
      hasPerishables,
      items: cartItems || []
    }]
  } else if (groups.length === 0) {
    groups = [{
      origin: 'Addis Ababa',
      destination: destinationName,
      distanceKm: calculateOriginDestinationDistanceKm('Addis Ababa', destinationName),
      weightKg: 1,
      hasPerishables: false,
      items: []
    }]
  }

  const isMulti = groups.length > 1
  const originNames = groups.map(g => g.origin).join(', ')

  const buildTierOption = (
    tierKey: 'standard' | 'express' | 'overnight',
    title: string,
    config: { flatRate: number; baseFee: number; ratePerKg: number; ratePerKm: number },
    tierPerishableOnly: boolean,
    requiresColdChain: boolean,
    estimatedDays: string,
    defaultTransitDays: string
  ): DynamicShippingOption => {
    // Calculate each origin group independently
    const routes: OriginShippingRoute[] = groups.map(group => {
      const calc = calculateTierFee(
        group.weightKg,
        group.distanceKm,
        group.origin,
        destinationName,
        config
      )
      return {
        origin: group.origin,
        destination: destinationName,
        distanceKm: group.distanceKm,
        weightKg: group.weightKg,
        itemCount: group.items.length,
        items: group.items,
        hasPerishables: group.hasPerishables,
        fee: calc.fee,
        isFlatRate: calc.breakdown.isFlatRate,
        formulaStr: calc.breakdown.formulaStr,
        breakdown: calc.breakdown
      }
    })

    // Sum all route fees across origin groups
    const totalFee = routes.reduce((sum, r) => sum + r.fee, 0)
    const primaryRoute = routes[0]

    let badge: string | null = null
    if (isMulti) {
      badge = `SPLIT DISPATCH (${groups.length} ORIGINS)`
    } else if (primaryRoute.isFlatRate) {
      badge = 'STANDARD FLAT RATE'
    } else {
      badge = 'DYNAMIC BULK MATRIX'
    }

    if (requiresColdChain) {
      badge = 'RECOMMENDED FOR FRESH PRODUCE'
    }

    let desc = ''
    if (isMulti) {
      desc = `Split shipment from ${groups.length} hubs (${originNames}) → ${destinationName}`
    } else if (primaryRoute.isFlatRate) {
      desc = `Ambient cargo dispatch • ${regionInfo.transitDays} (Flat rate: ${primaryRoute.origin} → ${destinationName})`
    } else {
      desc = `Ambient cargo dispatch • ${regionInfo.transitDays} (${primaryRoute.origin} → ${destinationName} • ${primaryRoute.weightKg} kg • ${primaryRoute.distanceKm} km)`
    }

    return {
      key: tierKey,
      title,
      desc,
      fee: totalFee,
      badge,
      isPerishableOnly: tierPerishableOnly,
      requiresColdChain,
      transitDays: defaultTransitDays,
      estimatedDays,
      routes,
      isMultiOrigin: isMulti,
      originCount: groups.length,
      breakdown: {
        ...primaryRoute.breakdown,
        totalFee
      }
    }
  }

  // 1. STANDARD FREIGHT (Flat 350, Base 200, 10 ETB/kg, 6 ETB/km)
  const stdOption = buildTierOption(
    'standard',
    'Standard Agricultural Freight',
    { flatRate: 350, baseFee: 200, ratePerKg: 10, ratePerKm: 6 },
    false,
    false,
    '3 - 5 business days',
    regionInfo.transitDays
  )

  // 2. EXPRESS DIRECT DISPATCH (Flat 600, Base 350, 16 ETB/kg, 10 ETB/km)
  const expOption = buildTierOption(
    'express',
    'Express Direct Dispatch',
    { flatRate: 600, baseFee: 350, ratePerKg: 16, ratePerKm: 10 },
    false,
    false,
    '1 - 2 business days',
    '1 - 2 business days'
  )

  const options: DynamicShippingOption[] = [stdOption, expOption]

  // 3. COLD-CHAIN AGRO TRANSPORT (Flat 1200, Base 700, 22 ETB/kg, 15 ETB/km)
  if (hasPerishables) {
    const coldOption = buildTierOption(
      'overnight',
      'Cold-Chain Agro Transport',
      { flatRate: 1200, baseFee: 700, ratePerKg: 22, ratePerKm: 15 },
      true,
      true,
      'Next-day delivery',
      'Guaranteed next-day cold delivery'
    )
    options.push(coldOption)
  }

  return options
}
