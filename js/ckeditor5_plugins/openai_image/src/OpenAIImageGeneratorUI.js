import { Plugin } from 'ckeditor5/src/core';
import { ButtonView } from 'ckeditor5/src/ui';
import ImageIcon from '../../../../icons/ai.svg';

export default class OpenAIImageGeneratorUI extends Plugin {
  init() {
    const editor = this.editor;

    // This will register the  toolbar button.
    editor.ui.componentFactory.add('openai_image', (locale) => {
      const buttonView = new ButtonView(locale);

      // Create the toolbar button.
      buttonView.set({
        label: editor.t('OpenAI Image Generator'),
        icon: ImageIcon,
        tooltip: true,
      });

      // Execute the command when the button is clicked (executed).
      this.listenTo(buttonView, 'execute', () =>
        editor.execute('generateOpenaiImage'),
      );

      return buttonView;
    });
  }
}
