import { Injectable } from '@nestjs/common';
import { 
  addDays, 
  subDays, 
  setHours, 
  setMinutes, 
  setSeconds, 
  isWithinInterval, 
  isBefore, 
  format,
  startOfMonth,
  addMonths,
  parseISO,
  differenceInSeconds
} from 'date-fns';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class ShiftService {
  constructor(private prisma: PrismaService) {}

  /**
   * Compute specific shift times based on shift name and reference date.
   */
  computeShiftTimes(shiftName: string, refDate: Date | string) {
    const date = typeof refDate === 'string' ? parseISO(refDate) : refDate;
    
    let start: Date;
    let end: Date;

    switch (shiftName?.toLowerCase().trim()) {
      case 'morning 8 hours female':
      case 'morning 8 hours':
        start = setSeconds(setMinutes(setHours(date, 7), 0), 0);
        end = setSeconds(setMinutes(setHours(date, 15), 0), 0);
        break;
      case 'day 12 hours':
        start = setSeconds(setMinutes(setHours(date, 7), 0), 0);
        end = setSeconds(setMinutes(setHours(date, 19), 0), 0);
        break;
      case 'evening 8 hours':
        start = setSeconds(setMinutes(setHours(date, 15), 0), 0);
        end = setSeconds(setMinutes(setHours(date, 23), 0), 0);
        break;
      case 'night 12 hours':
        start = setSeconds(setMinutes(setHours(date, 19), 0), 0);
        end = addDays(setSeconds(setMinutes(setHours(date, 7), 0), 0), 1);
        break;
      case 'night 8 hours':
        start = setSeconds(setMinutes(setHours(date, 23), 0), 0);
        end = addDays(setSeconds(setMinutes(setHours(date, 7), 0), 0), 1);
        break;
      default:
        start = setSeconds(setMinutes(setHours(date, 7), 0), 0);
        end = setSeconds(setMinutes(setHours(date, 15), 0), 0);
        break;
    }
    return { start, end };
  }

  getShiftBoundaries(now: Date = new Date()) {
    // Shift starts at 7 AM
    let shiftStart = setSeconds(setMinutes(setHours(now, 7), 0), 0);

    // If current time is before 7 AM, the shift started yesterday at 7 AM
    if (isBefore(now, shiftStart)) {
      shiftStart = subDays(shiftStart, 1);
    }

    const shiftEnd = addDays(shiftStart, 1);

    return { start: shiftStart, end: shiftEnd };
  }

  async countMyTokensToday(userId: bigint) {
    const { start, end } = this.getShiftBoundaries();
    return this.prisma.live_tokens.count({
      where: {
        user_id: userId,
        insert_time: { gte: start, lt: end }
      }
    });
  }

  async countMyTokensYesterday(userId: bigint) {
    const { start, end } = this.getShiftBoundaries();
    const yStart = subDays(start, 1);
    const yEnd = subDays(end, 1);
    return this.prisma.live_tokens.count({
      where: {
        user_id: userId,
        insert_time: { gte: yStart, lt: yEnd }
      }
    });
  }

  async countMyTokensMonth(userId: bigint) {
    const start = setHours(startOfMonth(new Date()), 7);
    const end = addMonths(start, 1);
    return this.prisma.live_tokens.count({
      where: {
        user_id: userId,
        insert_time: { gte: start, lt: end }
      }
    });
  }

  async countMyTokensLifetime(userId: bigint) {
    return this.prisma.live_tokens.count({
      where: { user_id: userId }
    });
  }

  async getDailyTokenCounts(userId: bigint, days: number = 7) {
    const dates: string[] = [];
    const counts: number[] = [];

    for (let i = days - 1; i >= 0; i--) {
      const date = subDays(new Date(), i);
      const { start, end } = this.getShiftBoundaries(date);

      const count = await this.prisma.live_tokens.count({
        where: {
          user_id: userId,
          insert_time: { gte: start, lt: end }
        }
      });

      dates.push(format(date, 'MMM dd'));
      counts.push(count);
    }

    return { labels: dates, data: counts };
  }
}
