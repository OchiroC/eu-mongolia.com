import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

/**
 * Tailwind класуудыг зөв нэгтгэх (shadcn-vue-ийн стандарт туслах).
 */
export function cn(...inputs) {
    return twMerge(clsx(inputs));
}
