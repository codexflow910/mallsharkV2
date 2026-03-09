import { createTheme  } from '@material-ui/core/styles';
import { BORDER_RADIUS } from '../utills/config';

export default createTheme({
  typography: {
    fontFamily: ['Source Code Pro', 'Helvetica', 'Arial', 'sans-serif'].join(
      ','
    ),
  },
  palette: {
    type: 'light',
    background: {
      default: '#ffffff',
      paper: '#ffffff',
    },
    primary: {
      main: '#ffffff',
    },
    secondary: {
      main: '#195dfa',
    },
  },
  overrides: {
    MuiButton: {
      root: {
        borderRadius: BORDER_RADIUS,
      },
    },
    MuiPaper: {
      rounded: {
        borderRadius: BORDER_RADIUS,
      },
    },
    MuiDialog: {
      paper: {
        borderRadius: BORDER_RADIUS,
      },
    },
    MuiInput: {
      underline: {
        '&:before': {
          borderBottomColor: '#313131',
        },
        '&:after': {
          borderBottomColor: '#313131',
        },
      },
    },
  },
});
