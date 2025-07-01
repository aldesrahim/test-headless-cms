import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import markdownEditorFormComponent from './components/markdown-editor';

Alpine.data('markdownEditorFormComponent', markdownEditorFormComponent);

Livewire.start();
