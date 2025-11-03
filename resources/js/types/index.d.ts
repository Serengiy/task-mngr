import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface UserResource {
    id: number;
    name: string;
    email: string;
}

export interface CommentResource {
    id: number;
    text: string;
    created_at: string;
    updated_at: string;
    user: UserResource;
}

export interface TaskResource {
    id: number;
    name: string;
    description: string;
    status: string;
    created_at: string;
    updated_at: string;
    due_date: string;
    creator: UserResource;
    responsible: UserResource;
    participants: UserResource[];
    comments: CommentResource[];
}


export type BreadcrumbItemType = BreadcrumbItem;
