import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import ListView from './ListView';
import dayjs from 'dayjs';

describe('ListView Component', () => {
  const mockProps = {
    UserFlow: 3,
    setUserFlow: jest.fn(),
    ListOfInstrucitons: [
      {
        dateAdjustment: 0,
        dateAdjustmentUnit: 'day',
        fromPg: false,
        isPg: false,
        lines: [
          { label: 'Inject 2cc Cystorelin (GnRH)' },
          { label: '<<ai_after_standing_heat>>' },
          { label: 'Apply CIDR device' },
          { label: '<<current_time>>' },
          { label: '' }
        ]
      },
      {
        dateAdjustment: 7,
        dateAdjustmentUnit: 'day',
        fromPg: false,
        isPg: true,
        lines: [
          { label: 'Remove CIDR' },
          { label: '<<cidr_device>>' },
          { label: '' },
          { label: '5cc Lutalyse (PG)' },
          { label: '' }
        ]
      }
    ],
    DateToStartBreeding: dayjs('2024-03-20T02:00:00'),
    SynchronizationProtocol: 1,
    GNRH: 'Cystorelin',
    PG: 'Lutalyse',
    SemenType: 'Conventional',
    BullTurnIn: '0'
  };

  const mockPropsWithSexed = {
    ...mockProps,
    SemenType: 'Conventional & Sexed',
    ListOfInstrucitons: [
      {
        dateAdjustment: 0,
        dateAdjustmentUnit: 'day',
        fromPg: false,
        isPg: false,
        lines: [
          { label: 'Inject 2cc Cystorelin (GnRH)' },
          { label: '<<ai_after_standing_heat>>' },
          { label: 'Apply CIDR device' },
          { label: '<<current_time>>' },
          { label: '' }
        ]
      },
      {
        dateAdjustment: 7,
        dateAdjustmentUnit: 'day',
        fromPg: false,
        isPg: true,
        lines: [
          { label: 'Remove CIDR' },
          { label: '<<cidr_device>>' },
          { label: '' },
          { label: '5cc Lutalyse (PG)' },
          { label: '' }
        ]
      }
    ]
  };

  it('renders protocol number in header', () => {
    render(<ListView {...mockProps} />);
    expect(screen.getByText('Protocol #1')).toBeInTheDocument();
  });

  it('renders table with date and instructions columns', () => {
    render(<ListView {...mockProps} />);
    expect(screen.getByText('Date')).toBeInTheDocument();
    expect(screen.getByText('Instructions')).toBeInTheDocument();
  });

  it('renders instructions with conventional semen type', () => {
    render(<ListView {...mockProps} />);
    expect(screen.getByText('Breed females AI 10-14 hours after standing heat.')).toBeInTheDocument();
  });

  it('renders instructions with sexed semen type', () => {
    render(<ListView {...mockPropsWithSexed} />);
    expect(screen.getByText('Breed females AI 16-22 hours after standing heat.')).toBeInTheDocument();
  });

  it('handles back button click', () => {
    render(<ListView {...mockProps} />);
    fireEvent.click(screen.getByText('Back'));
    expect(mockProps.setUserFlow).toHaveBeenCalledWith(2);
  });

  it('handles print button click', () => {
    const mockPrint = jest.spyOn(window, 'print').mockImplementation(() => {});
    render(<ListView {...mockProps} />);
    fireEvent.click(screen.getByText('Print'));
    expect(mockPrint).toHaveBeenCalled();
    mockPrint.mockRestore();
  });

  it('formats dates correctly in table', () => {
    render(<ListView {...mockProps} />);
    // Match the format used in the component (MM/DD/YYYY)
    expect(screen.getByText((content, element) => {
      return element.tagName.toLowerCase() === 'td' && content.includes('03/20/2024');
    })).toBeInTheDocument();
  });

  it('displays weekday names', () => {
    render(<ListView {...mockProps} />);
    const weekdayElements = screen.getAllByText((content, element) => {
      return element.tagName.toLowerCase() === 'td' && content.includes('Wednesday');
    });
    expect(weekdayElements.length).toBeGreaterThan(0);
  });

  it('handles different GNRH types', () => {
    const propsWithDifferentGNRH = {
      ...mockProps,
      GNRH: 'Factrel'
    };
    render(<ListView {...propsWithDifferentGNRH} />);
    expect(screen.getByText((content, element) => {
      return content.includes('2cc Factrel (GnRH)');
    })).toBeInTheDocument();
  });

  it('handles different PG types', () => {
    const propsWithDifferentPG = {
      ...mockProps,
      PG: 'Estrumate'
    };
    render(<ListView {...propsWithDifferentPG} />);
    expect(screen.getByText('2cc Estrumate (PG)')).toBeInTheDocument();
  });
});