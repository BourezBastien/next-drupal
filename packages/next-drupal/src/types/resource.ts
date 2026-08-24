import type { JsonApiError, JsonApiLinks } from "../jsonapi-errors"
import type { DrupalPathAlias } from "./drupal"

// JSON:API payloads are open-ended: unknown keeps consumers honest while
// preserving index access to server-controlled members.
export interface JsonApiResponse extends Record<string, unknown> {
  jsonapi?: {
    version: string
    meta: Record<string, unknown>[]
  }
  data: Record<string, unknown>[]
  errors: JsonApiError[]
  meta: {
    count: number
    [key: string]: unknown
  }
  links?: JsonApiLinks
  included?: Record<string, unknown>[]
}

export interface JsonApiResourceBodyRelationship {
  data:
    | {
        type: string
        id: string
        // Relationship metadata, e.g. alt text for media image references.
        meta?: Record<string, unknown>
      }
    | Array<{
        type: string
        id: string
        meta?: Record<string, unknown>
      }>
}

export interface JsonApiCreateResourceBody {
  data: {
    type?: string
    attributes?: Record<string, unknown>
    relationships?: Record<string, JsonApiResourceBodyRelationship>
  }
}

export interface JsonApiCreateFileResourceBody {
  data: {
    type?: string
    attributes: {
      type: string
      field: string
      filename: string
      file: Buffer
    }
  }
}

export interface JsonApiUpdateResourceBody {
  data: {
    type?: string
    id?: string
    attributes?: Record<string, unknown>
    relationships?: Record<string, JsonApiResourceBodyRelationship>
  }
}

export interface JsonApiResource extends Record<string, unknown> {
  id: string
  type: string
  langcode: string
  status: boolean
}

export interface JsonApiResourceWithPath extends JsonApiResource {
  path: DrupalPathAlias
}
