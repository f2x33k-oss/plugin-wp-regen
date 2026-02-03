// Content Types

export enum ContentType {
  RECIPE_ALBUM = 'recipe_album',
  IDEA_CAROUSEL = 'idea_carousel',
}

export interface ContentItemResponse {
  id: string;
  jobId: string;
  userId: string;
  contentType: string;
  title?: string;
  subtitle?: string;
  bodyHtml?: string;
  bodyMarkdown?: string;
  formatData: RecipeAlbumData | IdeaCarouselData | Record<string, any>;
  featuredImageUrl?: string;
  mediaUrls: string[];
  publishedTo: PublicationRecord[];
  seoTitle?: string;
  seoDescription?: string;
  keywords: string[];
  createdAt: string;
  updatedAt: string;
}

export interface PublicationRecord {
  platform: string;
  url: string;
  publishedAt: string;
}

export interface PublishRequest {
  platform: string;
  url: string;
  publishedAt: string;
}

// Recipe Album Format

export interface RecipeAlbumData {
  albumTitle: string;
  albumDescription: string;
  albumCoverImage: string;
  recipes: Recipe[];
  seo: SEOData;
}

export interface Recipe {
  id: string;
  title: string;
  description: string;
  prepTimeMinutes: number;
  cookTimeMinutes: number;
  servings: number;
  difficulty: 'facile' | 'moyen' | 'difficile';
  ingredients: Ingredient[];
  steps: RecipeStep[];
  featuredImage: string;
  nutritionInfo?: NutritionInfo;
  tags: string[];
}

export interface Ingredient {
  item: string;
  quantity: string;
  unit: string;
  notes?: string;
}

export interface RecipeStep {
  stepNumber: number;
  instruction: string;
  imageUrl?: string;
}

export interface NutritionInfo {
  calories: number;
  proteinG: number;
  carbsG: number;
  fatG: number;
}

// Idea Carousel Format

export interface IdeaCarouselData {
  carouselTitle: string;
  carouselDescription: string;
  slides: CarouselSlide[];
  carouselStyle: CarouselStyle;
  seo: SEOData;
}

export interface CarouselSlide {
  slideNumber: number;
  title: string;
  content: string;
  imageUrl: string;
  ctaText?: string;
  ctaUrl?: string;
  tags: string[];
}

export interface CarouselStyle {
  theme: string;
  primaryColor: string;
  font: string;
}

// Common

export interface SEOData {
  metaDescription: string;
  keywords: string[];
}
