import core from './core.js';
import projects from './projects.js';

export const views = Object.freeze({ core, projects });
export const defaultView = views.projects;

export function resolveView(search = window.location.search) {
    const key = new URLSearchParams(search).get('view');
    return Object.hasOwn(views, key) ? views[key] : defaultView;
}
